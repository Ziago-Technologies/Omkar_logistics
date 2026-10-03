<?php

namespace App\Http\Controllers;

use App\Models\Bilty;
use App\Models\BiltyItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\AccountLedger;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InvoiceController extends Controller
{
    /**
     * Determine default series based on top navbar Financial Year session (e.g. '2026-2027' -> '26-27').
     */
    protected function getDefaultSeries()
    {
        return Invoice::getCurrentSeries();
    }

    /**
     * Show the Invoice / Party Bill creation form.
     */
    public function create(Request $request)
    {
        if ($request->filled('invoice_id')) {
            return $this->edit($request->invoice_id);
        }

        // 1. Next Invoice No for series
        $defaultSeries = $this->getDefaultSeries();
        $series = strtoupper(trim($request->query('series', $defaultSeries)));
        $nextInvoiceNo = Invoice::getNextAvailableInvoiceNo($series);
        $seriesList = \App\Models\Series::orderBy('name', 'asc')->get();

        // 2. All Debtors / Creditors / Parties from account_ledgers for Account autocomplete
        $accounts = AccountLedger::whereIn('under_group', ['Debtors', 'Creditors', 'Direct Incomes', 'Indirect Incomes', 'Direct Expenses', 'Indirect Expenses'])
            ->orWhereNull('under_group')
            ->orderBy('ledger_name')
            ->get();

        // 3. Distinct Destinations for Destination filter
        $destinations = Location::orderBy('name')->pluck('name');

        // 4. Distinct Item descriptions for Item filter
        $itemDescriptions = BiltyItem::select('description')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->distinct()
            ->orderBy('description')
            ->pluck('description');

        // 4b. Distinct Units for Unit filter
        $units = \App\Models\MeasurementUnit::pluck('unit_code')
            ->merge(BiltyItem::select('unit')->whereNotNull('unit')->where('unit', '!=', '')->pluck('unit'))
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // 5. Build monthPartiesMap, party destinations, and party items for all unbilled bilties (TBB only)
        $allUnbilledBilties = Bilty::with(['consignor', 'billingParty', 'toLocation', 'toCity', 'items'])
            ->tbb()
            ->whereNull('invoice_id')
            ->whereNotNull('invoice_date')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $monthPartiesMap = [];
        $months = [];
        $allConsignors = [];
        $allPendingParties = [];
        $allDestinations = [];
        $allItems = [];
        $allPartyDestinations = [];
        $allPartyItems = [];

        foreach ($allUnbilledBilties as $b) {
            $shortM = $b->invoice_date->format('M/Y');
            $ym = $b->invoice_date->format('Y-m');
            $months[$ym] = $shortM;

            $consignorName = trim($b->consignor_name ?: ($b->consignor ? $b->consignor->ledger_name : ''));
            $billingPartyName = trim($b->billing_party_name ?: ($b->billingParty ? $b->billingParty->ledger_name : ''));
            $dest = trim($b->to_location_name);

            if (!isset($monthPartiesMap[$shortM])) {
                $monthPartiesMap[$shortM] = [
                    'consignors' => [],
                    'pending_parties' => [],
                    'destinations' => [],
                    'items' => [],
                    'party_destinations' => [],
                    'party_items' => [],
                ];
            }

            if ($consignorName !== '') {
                $monthPartiesMap[$shortM]['consignors'][$consignorName] = $consignorName;
                $monthPartiesMap[$shortM]['pending_parties'][$consignorName] = $consignorName;
                $allConsignors[$consignorName] = $consignorName;
                $allPendingParties[$consignorName] = $consignorName;

                if ($dest !== '') {
                    $monthPartiesMap[$shortM]['party_destinations'][$consignorName][$dest] = $dest;
                    $allPartyDestinations[$consignorName][$dest] = $dest;
                }
            }

            if ($billingPartyName !== '') {
                $monthPartiesMap[$shortM]['pending_parties'][$billingPartyName] = $billingPartyName;
                $allPendingParties[$billingPartyName] = $billingPartyName;

                if ($dest !== '') {
                    $monthPartiesMap[$shortM]['party_destinations'][$billingPartyName][$dest] = $dest;
                    $allPartyDestinations[$billingPartyName][$dest] = $dest;
                }
            }

            if ($dest !== '') {
                $monthPartiesMap[$shortM]['destinations'][$dest] = $dest;
                $allDestinations[$dest] = $dest;
            }

            foreach ($b->items as $it) {
                $itemDesc = trim($it->description);
                if ($itemDesc !== '') {
                    $monthPartiesMap[$shortM]['items'][$itemDesc] = $itemDesc;
                    $allItems[$itemDesc] = $itemDesc;

                    if ($consignorName !== '') {
                        $monthPartiesMap[$shortM]['party_items'][$consignorName][$itemDesc] = $itemDesc;
                        $allPartyItems[$consignorName][$itemDesc] = $itemDesc;
                    }

                    if ($billingPartyName !== '') {
                        $monthPartiesMap[$shortM]['party_items'][$billingPartyName][$itemDesc] = $itemDesc;
                        $allPartyItems[$billingPartyName][$itemDesc] = $itemDesc;
                    }
                }
            }
        }

        // Sort each month's arrays
        foreach ($monthPartiesMap as $k => $v) {
            ksort($monthPartiesMap[$k]['consignors'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['pending_parties'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['destinations'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['items'], SORT_NATURAL | SORT_FLAG_CASE);
            
            $monthPartiesMap[$k]['consignors'] = array_values($monthPartiesMap[$k]['consignors']);
            $monthPartiesMap[$k]['pending_parties'] = array_values($monthPartiesMap[$k]['pending_parties']);
            $monthPartiesMap[$k]['destinations'] = array_values($monthPartiesMap[$k]['destinations']);
            $monthPartiesMap[$k]['items'] = array_values($monthPartiesMap[$k]['items']);

            $pDests = [];
            foreach ($monthPartiesMap[$k]['party_destinations'] as $p => $dList) {
                ksort($dList, SORT_NATURAL | SORT_FLAG_CASE);
                $pDests[$p] = array_values($dList);
            }
            $monthPartiesMap[$k]['party_destinations'] = $pDests;

            $pItems = [];
            foreach ($monthPartiesMap[$k]['party_items'] as $p => $iList) {
                ksort($iList, SORT_NATURAL | SORT_FLAG_CASE);
                $pItems[$p] = array_values($iList);
            }
            $monthPartiesMap[$k]['party_items'] = $pItems;
        }

        ksort($allConsignors, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allPendingParties, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allDestinations, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allItems, SORT_NATURAL | SORT_FLAG_CASE);

        $allPDests = [];
        foreach ($allPartyDestinations as $p => $dList) {
            ksort($dList, SORT_NATURAL | SORT_FLAG_CASE);
            $allPDests[$p] = array_values($dList);
        }

        $allPItems = [];
        foreach ($allPartyItems as $p => $iList) {
            ksort($iList, SORT_NATURAL | SORT_FLAG_CASE);
            $allPItems[$p] = array_values($iList);
        }

        $allData = [
            'consignors' => array_values($allConsignors),
            'pending_parties' => array_values($allPendingParties),
            'destinations' => array_values($allDestinations),
            'items' => array_values($allItems),
            'party_destinations' => $allPDests,
            'party_items' => $allPItems,
        ];

        $monthPartiesMap[''] = $allData;
        $monthPartiesMap['all'] = $allData;
        $monthPartiesMap['-- All Months --'] = $allData;

        // Register aliases for case-insensitivity and 'Sept' -> 'Sep'
        foreach (array_keys($monthPartiesMap) as $mKey) {
            if ($mKey !== '' && $mKey !== 'all' && $mKey !== '-- All Months --') {
                $monthPartiesMap[strtolower($mKey)] = $monthPartiesMap[$mKey];
                $monthPartiesMap[strtoupper($mKey)] = $monthPartiesMap[$mKey];
                if (stripos($mKey, 'Sep/') === 0) {
                    $septKey = preg_replace('/^sep\//i', 'Sept/', $mKey);
                    $monthPartiesMap[$septKey] = $monthPartiesMap[$mKey];
                    $monthPartiesMap[strtolower($septKey)] = $monthPartiesMap[$mKey];
                }
            }
        }

        krsort($months);
        if (empty($months)) {
            $months[date('Y-m')] = date('M/Y');
        }

        // Default active month (latest month with unbilled bilties)
        $defaultMonth = reset($months);
        $selectedMonth = $request->query('month', $defaultMonth);
        if ($selectedMonth) {
            $selectedMonthNorm = preg_replace('/^sept\//i', 'Sep/', trim($selectedMonth));
            if (isset($monthPartiesMap[$selectedMonthNorm])) {
                $selectedMonth = $selectedMonthNorm;
            }
        }

        $currentMonthData = $monthPartiesMap[$selectedMonth] ?? ($monthPartiesMap[$defaultMonth] ?? ['consignors' => [], 'pending_parties' => [], 'destinations' => [], 'items' => [], 'party_destinations' => [], 'party_items' => []]);
        $consignors = $currentMonthData['consignors'];
        $pendingParties = $currentMonthData['pending_parties'];
        $locNames = Location::pluck('name')->toArray();
        $cityNames = \App\Models\CityModel::pluck('name')->toArray();
        $allLocationsList = collect(array_merge($locNames, $cityNames))->filter()->map(fn($v) => trim($v))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        $allConsigneesList = AccountLedger::orderBy('ledger_name')->pluck('ledger_name')->filter()->map(fn($v) => trim($v))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('invoice.create', compact(
            'series',
            'seriesList',
            'defaultSeries',
            'nextInvoiceNo',
            'accounts',
            'consignors',
            'pendingParties',
            'itemDescriptions',
            'units',
            'destinations',
            'months',
            'selectedMonth',
            'monthPartiesMap',
            'allLocationsList',
            'allConsigneesList'
        ));
    }

    /**
     * Show the Invoice / Party Bill edit form for an existing invoice.
     */
    public function edit($id)
    {
        $existingInvoice = Invoice::with(['items', 'account', 'consignor', 'bilties'])->findOrFail($id);

        $defaultSeries = $this->getDefaultSeries();
        $series = $existingInvoice->series ?: $defaultSeries;
        $nextInvoiceNo = $existingInvoice->invoice_no;
        $seriesList = \App\Models\Series::orderBy('name', 'asc')->get();

        // All Debtors / Creditors / Parties from account_ledgers
        $accounts = AccountLedger::whereIn('under_group', ['Debtors', 'Creditors', 'Direct Incomes', 'Indirect Incomes', 'Direct Expenses', 'Indirect Expenses'])
            ->orWhereNull('under_group')
            ->orderBy('ledger_name')
            ->get();

        // Distinct Destinations
        $destinations = Location::orderBy('name')->pluck('name');

        // Distinct Item descriptions
        $itemDescriptions = BiltyItem::select('description')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->distinct()
            ->orderBy('description')
            ->pluck('description');

        // Distinct Units for Unit filter
        $units = \App\Models\MeasurementUnit::pluck('unit_code')
            ->merge(BiltyItem::select('unit')->whereNotNull('unit')->where('unit', '!=', '')->pluck('unit'))
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Build monthPartiesMap for unbilled bilties (TBB only) PLUS any bilties attached to this invoice
        $allBilties = Bilty::with(['consignor', 'billingParty', 'toLocation', 'toCity', 'items'])
            ->where(function($q) use ($existingInvoice) {
                $q->where('invoice_id', $existingInvoice->id)
                  ->orWhere(function($sq) {
                      $sq->whereNull('invoice_id')->tbb();
                  });
            })
            ->whereNotNull('invoice_date')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $monthPartiesMap = [];
        $months = [];
        $allConsignors = [];
        $allPendingParties = [];
        $allDestinations = [];
        $allItems = [];
        $allPartyDestinations = [];
        $allPartyItems = [];

        foreach ($allBilties as $b) {
            $shortM = $b->invoice_date->format('M/Y');
            $ym = $b->invoice_date->format('Y-m');
            $months[$ym] = $shortM;

            $consignorName = trim($b->consignor_name ?: ($b->consignor ? $b->consignor->ledger_name : ''));
            $billingPartyName = trim($b->billing_party_name ?: ($b->billingParty ? $b->billingParty->ledger_name : ''));
            $dest = trim($b->to_location_name);

            if (!isset($monthPartiesMap[$shortM])) {
                $monthPartiesMap[$shortM] = [
                    'consignors' => [],
                    'pending_parties' => [],
                    'destinations' => [],
                    'items' => [],
                    'party_destinations' => [],
                    'party_items' => [],
                ];
            }

            if ($consignorName !== '') {
                $monthPartiesMap[$shortM]['consignors'][$consignorName] = $consignorName;
                $monthPartiesMap[$shortM]['pending_parties'][$consignorName] = $consignorName;
                $allConsignors[$consignorName] = $consignorName;
                $allPendingParties[$consignorName] = $consignorName;

                if ($dest !== '') {
                    $monthPartiesMap[$shortM]['party_destinations'][$consignorName][$dest] = $dest;
                    $allPartyDestinations[$consignorName][$dest] = $dest;
                }
            }

            if ($billingPartyName !== '') {
                $monthPartiesMap[$shortM]['pending_parties'][$billingPartyName] = $billingPartyName;
                $allPendingParties[$billingPartyName] = $billingPartyName;

                if ($dest !== '') {
                    $monthPartiesMap[$shortM]['party_destinations'][$billingPartyName][$dest] = $dest;
                    $allPartyDestinations[$billingPartyName][$dest] = $dest;
                }
            }

            if ($dest !== '') {
                $monthPartiesMap[$shortM]['destinations'][$dest] = $dest;
                $allDestinations[$dest] = $dest;
            }

            foreach ($b->items as $it) {
                $itemDesc = trim($it->description);
                if ($itemDesc !== '') {
                    $monthPartiesMap[$shortM]['items'][$itemDesc] = $itemDesc;
                    $allItems[$itemDesc] = $itemDesc;

                    if ($consignorName !== '') {
                        $monthPartiesMap[$shortM]['party_items'][$consignorName][$itemDesc] = $itemDesc;
                        $allPartyItems[$consignorName][$itemDesc] = $itemDesc;
                    }

                    if ($billingPartyName !== '') {
                        $monthPartiesMap[$shortM]['party_items'][$billingPartyName][$itemDesc] = $itemDesc;
                        $allPartyItems[$billingPartyName][$itemDesc] = $itemDesc;
                    }
                }
            }
        }

        foreach ($monthPartiesMap as $k => $v) {
            ksort($monthPartiesMap[$k]['consignors'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['pending_parties'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['destinations'], SORT_NATURAL | SORT_FLAG_CASE);
            ksort($monthPartiesMap[$k]['items'], SORT_NATURAL | SORT_FLAG_CASE);

            $monthPartiesMap[$k]['consignors'] = array_values($monthPartiesMap[$k]['consignors']);
            $monthPartiesMap[$k]['pending_parties'] = array_values($monthPartiesMap[$k]['pending_parties']);
            $monthPartiesMap[$k]['destinations'] = array_values($monthPartiesMap[$k]['destinations']);
            $monthPartiesMap[$k]['items'] = array_values($monthPartiesMap[$k]['items']);

            $pDests = [];
            foreach ($monthPartiesMap[$k]['party_destinations'] as $p => $dList) {
                ksort($dList, SORT_NATURAL | SORT_FLAG_CASE);
                $pDests[$p] = array_values($dList);
            }
            $monthPartiesMap[$k]['party_destinations'] = $pDests;

            $pItems = [];
            foreach ($monthPartiesMap[$k]['party_items'] as $p => $iList) {
                ksort($iList, SORT_NATURAL | SORT_FLAG_CASE);
                $pItems[$p] = array_values($iList);
            }
            $monthPartiesMap[$k]['party_items'] = $pItems;
        }

        ksort($allConsignors, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allPendingParties, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allDestinations, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($allItems, SORT_NATURAL | SORT_FLAG_CASE);

        $allPDests = [];
        foreach ($allPartyDestinations as $p => $dList) {
            ksort($dList, SORT_NATURAL | SORT_FLAG_CASE);
            $allPDests[$p] = array_values($dList);
        }

        $allPItems = [];
        foreach ($allPartyItems as $p => $iList) {
            ksort($iList, SORT_NATURAL | SORT_FLAG_CASE);
            $allPItems[$p] = array_values($iList);
        }

        $allData = [
            'consignors' => array_values($allConsignors),
            'pending_parties' => array_values($allPendingParties),
            'destinations' => array_values($allDestinations),
            'items' => array_values($allItems),
            'party_destinations' => $allPDests,
            'party_items' => $allPItems,
        ];

        $monthPartiesMap[''] = $allData;
        $monthPartiesMap['all'] = $allData;
        $monthPartiesMap['-- All Months --'] = $allData;

        // Register aliases for case-insensitivity and 'Sept' -> 'Sep'
        foreach (array_keys($monthPartiesMap) as $mKey) {
            if ($mKey !== '' && $mKey !== 'all' && $mKey !== '-- All Months --') {
                $monthPartiesMap[strtolower($mKey)] = $monthPartiesMap[$mKey];
                $monthPartiesMap[strtoupper($mKey)] = $monthPartiesMap[$mKey];
                if (stripos($mKey, 'Sep/') === 0) {
                    $septKey = preg_replace('/^sep\//i', 'Sept/', $mKey);
                    $monthPartiesMap[$septKey] = $monthPartiesMap[$mKey];
                    $monthPartiesMap[strtolower($septKey)] = $monthPartiesMap[$mKey];
                }
            }
        }

        krsort($months);
        if (empty($months)) {
            $months[date('Y-m')] = date('M/Y');
        }

        $selectedMonth = $existingInvoice->for_month ?: reset($months);
        if ($selectedMonth) {
            $selectedMonthNorm = preg_replace('/^sept\//i', 'Sep/', trim($selectedMonth));
            if (isset($monthPartiesMap[$selectedMonthNorm])) {
                $selectedMonth = $selectedMonthNorm;
            }
        }

        $currentMonthData = $monthPartiesMap[$selectedMonth] ?? ['consignors' => [], 'pending_parties' => [], 'destinations' => [], 'items' => [], 'party_destinations' => [], 'party_items' => []];
        $consignors = $currentMonthData['consignors'];
        $pendingParties = $currentMonthData['pending_parties'];

        $locNames = Location::pluck('name')->toArray();
        $cityNames = \App\Models\CityModel::pluck('name')->toArray();
        $allLocationsList = collect(array_merge($locNames, $cityNames))->filter()->map(fn($v) => trim($v))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        $allConsigneesList = AccountLedger::orderBy('ledger_name')->pluck('ledger_name')->filter()->map(fn($v) => trim($v))->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();

        $isEdit = true;

        return view('invoice.create', compact(
            'existingInvoice',
            'isEdit',
            'series',
            'seriesList',
            'defaultSeries',
            'nextInvoiceNo',
            'accounts',
            'consignors',
            'pendingParties',
            'itemDescriptions',
            'units',
            'destinations',
            'months',
            'selectedMonth',
            'monthPartiesMap',
            'allLocationsList',
            'allConsigneesList'
        ));
    }

    /**
     * Helper to extract Consignors, Pending Parties, Destinations, and Items for a specific month.
     */
    public function extractPartiesForMonth($monthStr = null)
    {
        $query = Bilty::with(['consignor', 'billingParty', 'toLocation', 'toCity', 'items'])
            ->tbb()
            ->whereNull('invoice_id');

        if (!empty($monthStr) && $monthStr !== 'all' && $monthStr !== '-- All Months --') {
            $monthStr = trim($monthStr);
            if (preg_match('/^(\d{4})-(\d{2})$/', $monthStr, $matches)) {
                $query->whereYear('invoice_date', $matches[1])
                      ->whereMonth('invoice_date', $matches[2]);
            } elseif (preg_match('/^([A-Za-z]{3})\/(\d{4})$/', $monthStr, $matches)) {
                try {
                    $date = Carbon::createFromFormat('M/Y', $monthStr);
                    $query->whereYear('invoice_date', $date->year)
                          ->whereMonth('invoice_date', $date->month);
                } catch (\Exception $e) {
                }
            } elseif (preg_match('/^([A-Za-z]+)\/(\d{4})$/', $monthStr, $matches)) {
                try {
                    $date = Carbon::createFromFormat('F/Y', $monthStr);
                    $query->whereYear('invoice_date', $date->year)
                          ->whereMonth('invoice_date', $date->month);
                } catch (\Exception $e) {
                }
            }
        }

        $bilties = $query->get();

        $consignors = [];
        $pendingParties = [];
        $destinations = [];
        $items = [];
        $partyDestinations = [];
        $partyItems = [];

        foreach ($bilties as $b) {
            $consignorName = trim($b->consignor_name ?: ($b->consignor ? $b->consignor->ledger_name : ''));
            $billingPartyName = trim($b->billing_party_name ?: ($b->billingParty ? $b->billingParty->ledger_name : ''));
            $dest = trim($b->to_location_name);

            if ($consignorName !== '') {
                $consignors[$consignorName] = $consignorName;
                $pendingParties[$consignorName] = $consignorName;
                if ($dest !== '') {
                    $partyDestinations[$consignorName][$dest] = $dest;
                }
            }

            if ($billingPartyName !== '') {
                $pendingParties[$billingPartyName] = $billingPartyName;
                if ($dest !== '') {
                    $partyDestinations[$billingPartyName][$dest] = $dest;
                }
            }

            if ($dest !== '') {
                $destinations[$dest] = $dest;
            }

            foreach ($b->items as $it) {
                $itemDesc = trim($it->description);
                if ($itemDesc !== '') {
                    $items[$itemDesc] = $itemDesc;
                    if ($consignorName !== '') {
                        $partyItems[$consignorName][$itemDesc] = $itemDesc;
                    }
                    if ($billingPartyName !== '') {
                        $partyItems[$billingPartyName][$itemDesc] = $itemDesc;
                    }
                }
            }
        }

        ksort($consignors, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($pendingParties, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($destinations, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($items, SORT_NATURAL | SORT_FLAG_CASE);

        $pDests = [];
        foreach ($partyDestinations as $p => $dList) {
            ksort($dList, SORT_NATURAL | SORT_FLAG_CASE);
            $pDests[$p] = array_values($dList);
        }

        $pItems = [];
        foreach ($partyItems as $p => $iList) {
            ksort($iList, SORT_NATURAL | SORT_FLAG_CASE);
            $pItems[$p] = array_values($iList);
        }

        return [
            'consignors' => array_values($consignors),
            'pending_parties' => array_values($pendingParties),
            'destinations' => array_values($destinations),
            'items' => array_values($items),
            'party_destinations' => $pDests,
            'party_items' => $pItems,
        ];
    }

    /**
     * AJAX API: Lookup an existing invoice by Invoice / Receipt No and Series.
     */
    public function lookup(Request $request, $invoice_no)
    {
        $series = $request->query('series');
        $parsedNo = Invoice::parseInvoiceNo($invoice_no);
        
        $query = Invoice::with(['items', 'account', 'consignor', 'user']);
        if ($series) {
            $query->where('series', $series);
        }
        $invoice = $query->where('invoice_no', $parsedNo)->first();

        // If not found with series, try finding just by invoice_no as fallback
        if (!$invoice && $series) {
            $invoice = Invoice::with(['items', 'account', 'consignor', 'user'])->where('invoice_no', $parsedNo)->first();
        }

        if (!$invoice) {
            return response()->json(['found' => false, 'message' => 'Invoice not found'], 404);
        }

        $rows = [];
        foreach ($invoice->items as $idx => $item) {
            $rows[] = [
                'bilty_id' => $item->bilty_id,
                'sr_no' => $item->sr_no ?: ($idx + 1),
                'date' => $item->bilty_date ? $item->bilty_date->format('d-m-Y') : '',
                'bilty_no' => $item->bilty_no,
                'cn_no' => $item->cn_no ?? '',
                'packages' => (int)$item->packages,
                'from_location' => $item->from_location ?? '',
                'to_location' => $item->to_location ?? '',
                'consignee_name' => $item->consignee_name ?? '',
                'item_description' => $item->item_description ?? '',
                'invoice_no_ref' => $item->invoice_no_ref ?? '',
                'weight' => number_format((float)$item->weight, 3, '.', ''),
                'weight_type' => $item->weight_type ?: 'KG',
                'rate' => number_format((float)$item->rate, 2, '.', ''),
                'st_charge' => number_format((float)$item->st_charge, 2, '.', ''),
                'freight_amount' => number_format((float)$item->freight_amount, 2, '.', ''),
                'unload_rate' => number_format((float)$item->unload_rate, 2, '.', ''),
                'unload_amount' => number_format((float)$item->unload_amount, 2, '.', ''),
                'other_charges' => number_format((float)$item->other_charges, 2, '.', ''),
                'oda_charge' => number_format((float)$item->oda_charge, 2, '.', ''),
                'amount' => number_format((float)$item->amount, 2, '.', ''),
            ];
        }

        $invoiceData = [
            'id' => $invoice->id,
            'series' => $invoice->series,
            'invoice_no' => $invoice->formatted_invoice_no,
            'raw_invoice_no' => $invoice->invoice_no,
            'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '',
            'account_name' => $invoice->account_name,
            'consignor_name' => $invoice->consignor_name ?? '',
            'for_month' => $invoice->for_month ?? '',
            'item_filter' => $invoice->item_filter ?? '',
            'is_gst_bill' => (bool)$invoice->is_gst_bill,
            'is_igst' => (bool)$invoice->is_igst,
            'destination_filter' => $invoice->destination_filter ?? '',
            'unit_filter' => $invoice->unit_filter ?? '',
            'bill_amount' => number_format((float)$invoice->bill_amount, 2, '.', ''),
            'gst_percent' => number_format((float)$invoice->gst_percent, 2, '.', ''),
            'gst_amount' => number_format((float)$invoice->gst_amount, 2, '.', ''),
            'total_amount' => number_format((float)$invoice->total_amount, 2, '.', ''),
            'remark' => $invoice->remark ?? '',
            'status' => $invoice->status ?: 'finalized',
            'user' => $invoice->user ? ($invoice->user->username ?: $invoice->user->name) : 'Administrator',
        ];

        return response()->json([
            'found' => true,
            'invoice' => $invoiceData,
            'rows' => $rows
        ]);
    }

    /**
     * AJAX API: Get consignors and pending bill parties for a specific month.
     */
    public function getMonthParties(Request $request)
    {
        $data = $this->extractPartiesForMonth($request->month);
        return response()->json($data);
    }

    /**
     * AJAX API: Update a bilty item record directly in the database when edited in invoice grid.
     */
    public function updateBiltyItem(Request $request)
    {
        $biltyId = $request->input('bilty_id');
        if (!$biltyId) {
            return response()->json(['success' => false, 'message' => 'bilty_id required'], 400);
        }

        $bilty = Bilty::with('items')->find($biltyId);
        if (!$bilty) {
            return response()->json(['success' => false, 'message' => 'Bilty not found'], 404);
        }

        $item = $bilty->items->first();
        if (!$item) {
            $item = BiltyItem::create([
                'bilty_id' => $bilty->id,
                'description' => 'Goods',
                'no_of_pkgs' => $bilty->total_packages ?: 1,
                'weight_val' => (float)$bilty->total_qty,
                'qty' => (float)$bilty->total_qty,
                'unit' => 'KG',
                'rate' => 0.00,
            ]);
        }

        if ($request->has('rate')) {
            $item->rate = (float)$request->input('rate');
        }

        if ($request->has('unload_rate')) {
            $item->unload_rate = (float)$request->input('unload_rate');
        }

        if ($request->has('unload_amount')) {
            $item->unload_amount = (float)$request->input('unload_amount');
        }

        if ($request->has('st_charge')) {
            $bilty->st_charge = (float)$request->input('st_charge');
            $item->st = (float)$request->input('st_charge');
        }

        if ($request->has('freight_amount')) {
            $bilty->gross_amount = (float)$request->input('freight_amount');
        }

        if ($request->has('other_charges')) {
            $bilty->rc_charge = (float)$request->input('other_charges');
            $bilty->sc_charge = 0;
            $item->rc = (float)$request->input('other_charges');
        }

        if ($request->has('oda_charge')) {
            $bilty->dd_charge = (float)$request->input('oda_charge');
            $item->dd = (float)$request->input('oda_charge');
        }

        if ($request->has('amount')) {
            $bilty->net_amount = (float)$request->input('amount');
        }

        if ($request->has('packages')) {
            $bilty->total_packages = (int)$request->input('packages');
            $item->no_of_pkgs = (int)$request->input('packages');
        }

        if ($request->has('weight')) {
            $bilty->total_qty = (float)$request->input('weight');
            $item->weight_val = (float)$request->input('weight');
            $item->qty = (float)$request->input('weight');
        }

        if ($request->has('weight_type')) {
            $item->unit = strtoupper($request->input('weight_type'));
        }

        if ($request->has('item_description')) {
            $item->description = $request->input('item_description');
        }

        if ($request->has('invoice_no_ref')) {
            $item->invoice_no = $request->input('invoice_no_ref');
        }

        if ($request->has('from_location') && !empty($request->input('from_location'))) {
            $fromName = trim($request->input('from_location'));
            $loc = \App\Models\Location::where('name', $fromName)->first();
            if ($loc) {
                $bilty->from_location_id = $loc->id;
            }
        }

        if ($request->has('to_location') && !empty($request->input('to_location'))) {
            $toName = trim($request->input('to_location'));
            $loc = \App\Models\Location::where('name', $toName)->first();
            if ($loc) {
                $bilty->to_location_id = $loc->id;
            } else {
                $city = \App\Models\CityModel::where('name', $toName)->first();
                if ($city) {
                    $bilty->to_city_id = $city->id;
                }
            }
        }

        if ($request->has('consignee_name') && !empty($request->input('consignee_name'))) {
            $cName = trim($request->input('consignee_name'));
            $con = \App\Models\AccountLedger::where('ledger_name', $cName)->first();
            if ($con) {
                $bilty->consignee_id = $con->id;
            }
        }

        $item->save();
        $bilty->save();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX API: Get pending (unbilled) bilties for selected filters.
     */
    public function getPendingBilties(Request $request)
    {
        $query = Bilty::with(['fromLocation', 'toLocation', 'toCity', 'consignor', 'consignee', 'billingParty', 'items']);

        if ($request->filled('invoice_id')) {
            $invId = (int)$request->invoice_id;
            $query->where(function($q) use ($invId) {
                $q->where('invoice_id', $invId)
                  ->orWhere(function($sq) {
                      $sq->whereNull('invoice_id')->tbb();
                  });
            });
        } else {
            $query->whereNull('invoice_id')->tbb();
        }

        // Filter by Account / Party name or Consignor using EXACT equality (=)
        $pName = $request->filled('party_name') ? trim($request->party_name) : '';
        $cName = $request->filled('consignor_name') ? trim($request->consignor_name) : '';

        if ($pName !== '' && $cName !== '' && $pName === $cName) {
            $query->where(function($q) use ($pName) {
                $q->where('billing_party_name', '=', $pName)
                  ->orWhere('consignor_name', '=', $pName)
                  ->orWhereHas('billingParty', function($sq) use ($pName) {
                      $sq->where('ledger_name', '=', $pName);
                  })
                  ->orWhereHas('consignor', function($sq) use ($pName) {
                      $sq->where('ledger_name', '=', $pName);
                  });
            });
        } else {
            if ($pName !== '') {
                $query->where(function($q) use ($pName) {
                    $q->where('billing_party_name', '=', $pName)
                      ->orWhere('consignor_name', '=', $pName)
                      ->orWhereHas('billingParty', function($sq) use ($pName) {
                          $sq->where('ledger_name', '=', $pName);
                      })
                      ->orWhereHas('consignor', function($sq) use ($pName) {
                          $sq->where('ledger_name', '=', $pName);
                      });
                });
            }

            if ($cName !== '') {
                $query->where(function($q) use ($cName) {
                    $q->where('consignor_name', '=', $cName)
                      ->orWhere('billing_party_name', '=', $cName)
                      ->orWhereHas('consignor', function($sq) use ($cName) {
                          $sq->where('ledger_name', '=', $cName);
                      })
                      ->orWhereHas('billingParty', function($sq) use ($cName) {
                          $sq->where('ledger_name', '=', $cName);
                      });
                });
            }
        }

        // Filter by Month (e.g. '2026-09' or 'Sep/2026')
        if ($request->filled('for_month') && $request->for_month !== 'all' && $request->for_month !== '-- All Months --') {
            $monthStr = $request->for_month;
            if (preg_match('/^(\d{4})-(\d{2})$/', $monthStr, $matches)) {
                $query->whereYear('invoice_date', $matches[1])
                      ->whereMonth('invoice_date', $matches[2]);
            } elseif (preg_match('/^([A-Za-z]{3})\/(\d{4})$/', $monthStr, $matches)) {
                try {
                    $date = Carbon::createFromFormat('M/Y', $monthStr);
                    $query->whereYear('invoice_date', $date->year)
                          ->whereMonth('invoice_date', $date->month);
                } catch (\Exception $e) {
                }
            }
        }

        // Filter by Destination using EXACT equality (=)
        if ($request->filled('destination')) {
            $dest = trim($request->destination);
            $query->where(function($q) use ($dest) {
                $q->whereHas('toCity', function($sq) use ($dest) {
                    $sq->where('name', '=', $dest);
                })->orWhereHas('toLocation', function($sq) use ($dest) {
                    $sq->where('name', '=', $dest);
                });
            });
        }

        // Filter by Item Description using EXACT equality (=)
        if ($request->filled('item_description')) {
            $itemDesc = trim($request->item_description);
            $query->whereHas('items', function($sq) use ($itemDesc) {
                $sq->where('description', '=', $itemDesc);
            });
        }

        // Filter by Unit Category
        if ($request->filled('unit_filter')) {
            $unitVal = strtoupper(trim($request->unit_filter));
            $query->whereHas('items', function($sq) use ($unitVal) {
                $sq->where(DB::raw('UPPER(unit)'), $unitVal);
            });
        } elseif ($request->filled('unit')) {
            $unitVal = strtoupper(trim($request->unit));
            $query->whereHas('items', function($sq) use ($unitVal) {
                $sq->where(DB::raw('UPPER(unit)'), $unitVal);
            });
        }

        $bilties = $query->orderBy('invoice_date', 'asc')->orderBy('bilty_no', 'asc')->get();

        $savedInvoiceItems = [];
        if ($request->filled('invoice_id')) {
            $savedInvoiceItems = InvoiceItem::where('invoice_id', (int)$request->invoice_id)->get()->keyBy('bilty_id');
        }

        $rows = [];
        $srNo = 1;

        foreach ($bilties as $b) {
            $item = $b->items->first();
            $itemDesc = $b->items->pluck('description')->filter()->implode(', ');
            $invNoRef = $b->items->pluck('invoice_no')->filter()->implode(', ');
            
            $packagesVal = (int)($b->total_packages ?: ($item ? $item->no_of_pkgs : 1));
            $weightVal = $b->total_qty > 0 ? (float)$b->total_qty : ($item ? (float)$item->weight_val : 0.0);
            $weightType = ($item && !empty($item->unit)) ? strtoupper(trim($item->unit)) : 'KG';
            $rateVal = $item ? (float)$item->rate : 0.0;
            $stCharge = (float)$b->st_charge;
            $grossAmount = (float)$b->gross_amount;
            if ($grossAmount <= 0 && $rateVal > 0) {
                if ($weightType === 'KG') {
                    $grossAmount = $weightVal * $rateVal;
                } else {
                    $grossAmount = $packagesVal * $rateVal;
                }
            }
            $unloadRate = $item ? (float)$item->unload_rate : 0.0;
            $unloadAmount = $item ? (float)$item->unload_amount : 0.0;
            $odaCharge = (float)$b->dd_charge;
            $otherCharges = (float)($b->rc_charge + $b->sc_charge);
            $totalRowAmt = (float)$b->net_amount;
            if ($totalRowAmt <= 0) {
                $totalRowAmt = $grossAmount + $stCharge + $otherCharges + $odaCharge + $unloadAmount;
            }

            // Override with saved InvoiceItem values if this bilty is saved in the invoice
            if (isset($savedInvoiceItems[$b->id])) {
                $savedItem = $savedInvoiceItems[$b->id];
                $packagesVal = (int)$savedItem->packages;
                $weightVal = (float)$savedItem->weight;
                $weightType = $savedItem->weight_type ?: $weightType;
                $rateVal = (float)$savedItem->rate;
                $stCharge = (float)$savedItem->st_charge;
                $grossAmount = (float)$savedItem->freight_amount;
                $unloadRate = (float)$savedItem->unload_rate;
                $unloadAmount = (float)$savedItem->unload_amount;
                $otherCharges = (float)$savedItem->other_charges;
                $odaCharge = (float)$savedItem->oda_charge;
                $totalRowAmt = (float)$savedItem->amount;
                if (!empty($savedItem->item_description)) $itemDesc = $savedItem->item_description;
                if (!empty($savedItem->invoice_no_ref)) $invNoRef = $savedItem->invoice_no_ref;
            }

            $rows[] = [
                'bilty_id' => $b->id,
                'sr_no' => $srNo++,
                'date' => $b->invoice_date ? $b->invoice_date->format('d-m-Y') : '',
                'bilty_no' => $b->bilty_no,
                'cn_no' => $b->cn_no ?? '',
                'packages' => $b->total_packages ?: ($item ? $item->no_of_pkgs : 1),
                'from_location' => $b->from_location_name ?: ($b->fromLocation ? $b->fromLocation->name : ''),
                'to_location' => $b->to_location_name ?: ($b->toLocation ? $b->toLocation->name : ''),
                'consignee_name' => $b->consignee_name ?: ($b->consignee ? $b->consignee->ledger_name : ''),
                'item_description' => $itemDesc ?: 'Goods',
                'invoice_no_ref' => $invNoRef ?: ($b->cn_no ?? ''),
                'weight' => number_format($weightVal, 3, '.', ''),
                'weight_type' => $weightType,
                'rate' => number_format($rateVal, 2, '.', ''),
                'st_charge' => number_format($stCharge, 2, '.', ''),
                'freight_amount' => number_format($grossAmount, 2, '.', ''),
                'unload_rate' => number_format($unloadRate, 2, '.', ''),
                'unload_amount' => number_format($unloadAmount, 2, '.', ''),
                'other_charges' => number_format($otherCharges, 2, '.', ''),
                'oda_charge' => number_format($odaCharge, 2, '.', ''),
                'amount' => number_format($totalRowAmt, 2, '.', ''),
            ];
        }

        return response()->json([
            'count' => count($rows),
            'rows' => $rows
        ]);
    }

    /**
     * Store a new invoice and update related bilties.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required',
            'invoice_date' => 'required|date',
            'series' => 'nullable|string',
            'account_name' => 'required|string',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $defaultSeries = $this->getDefaultSeries();
            $series = $request->filled('series') ? strtoupper(trim($request->series)) : $defaultSeries;
            $invoiceNo = Invoice::parseInvoiceNo($request->invoice_no);

            // Check duplicate
            $existing = Invoice::where('series', $series)->where('invoice_no', $invoiceNo)->first();
            if ($existing) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invoice #{$series}-{$invoiceNo} already exists. Please choose a unique Invoice No."
                    ], 422);
                }
                return back()->with('error', "Invoice #{$series}-{$invoiceNo} already exists. Please choose a unique Invoice No.")->withInput();
            }

            // Find account ledger if exists
            $account = AccountLedger::where('ledger_name', $request->account_name)->first();
            $consignor = $request->filled('consignor_name') ? AccountLedger::where('ledger_name', $request->consignor_name)->first() : null;

            $billAmount = (float)$request->bill_amount;
            $gstPercent = (float)$request->gst_percent;
            $gstAmount = (float)$request->gst_amount;
            $totalAmount = (float)$request->total_amount;

            $status = $request->input('status', 'finalized');
            if (!in_array($status, ['draft', 'finalized', 'cancelled'])) {
                $status = 'finalized';
            }

            $invoice = Invoice::create([
                'series' => $series,
                'invoice_no' => $invoiceNo,
                'invoice_date' => $request->invoice_date,
                'account_id' => $account ? $account->id : null,
                'account_name' => $request->account_name,
                'consignor_id' => $consignor ? $consignor->id : null,
                'consignor_name' => $request->consignor_name,
                'for_month' => $request->for_month,
                'item_filter' => $request->item_filter,
                'is_gst_bill' => $request->boolean('is_gst_bill'),
                'is_igst' => $request->boolean('is_igst'),
                'destination_filter' => $request->destination_filter,
                'unit_filter' => $request->unit_filter,
                'bill_amount' => $billAmount,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'total_amount' => $totalAmount,
                'remark' => $request->remark,
                'status' => $status,
                'user_id' => auth()->id(),
            ]);

            // Save items & attach/update bilties
            $biltyIds = [];
            foreach ($request->items as $idx => $itemData) {
                $biltyId = !empty($itemData['bilty_id']) ? (int)$itemData['bilty_id'] : null;
                if ($biltyId) {
                    $biltyIds[] = $biltyId;
                }

                $biltyDate = null;
                if (!empty($itemData['date'])) {
                    try {
                        $biltyDate = Carbon::parse($itemData['date'])->toDateString();
                    } catch (\Exception $e) {}
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'bilty_id' => $biltyId,
                    'sr_no' => $idx + 1,
                    'bilty_date' => $biltyDate,
                    'bilty_no' => $itemData['bilty_no'] ?? null,
                    'cn_no' => $itemData['cn_no'] ?? null,
                    'packages' => (int)($itemData['packages'] ?? 0),
                    'from_location' => $itemData['from_location'] ?? null,
                    'to_location' => $itemData['to_location'] ?? null,
                    'consignee_name' => $itemData['consignee_name'] ?? null,
                    'item_description' => $itemData['item_description'] ?? null,
                    'invoice_no_ref' => $itemData['invoice_no_ref'] ?? null,
                    'weight' => (float)($itemData['weight'] ?? 0),
                    'weight_type' => $itemData['weight_type'] ?? 'KG',
                    'rate' => (float)($itemData['rate'] ?? 0),
                    'st_charge' => (float)($itemData['st_charge'] ?? 0),
                    'freight_amount' => (float)($itemData['freight_amount'] ?? 0),
                    'unload_rate' => (float)($itemData['unload_rate'] ?? 0),
                    'unload_amount' => (float)($itemData['unload_amount'] ?? 0),
                    'other_charges' => (float)($itemData['other_charges'] ?? 0),
                    'oda_charge' => (float)($itemData['oda_charge'] ?? 0),
                    'amount' => (float)($itemData['amount'] ?? 0),
                ]);

                // Update the original Bilty (C.N.) record if attached
                if ($biltyId) {
                    $bilty = Bilty::with('items')->find($biltyId);
                    if ($bilty) {
                        $bilty->invoice_id = $invoice->id;
                        if ($biltyDate) {
                            $bilty->invoice_date = $biltyDate;
                        }

                        if (isset($itemData['cn_no'])) {
                            $bilty->cn_no = $itemData['cn_no'];
                        }

                        if (isset($itemData['packages'])) {
                            $bilty->total_packages = (int)$itemData['packages'];
                        }

                        if (!empty($itemData['from_location'])) {
                            $fromLocName = mb_strtoupper(trim($itemData['from_location']), 'UTF-8');
                            $loc = Location::firstOrCreate(['name' => $fromLocName]);
                            $bilty->from_location_id = $loc->id;
                        }

                        if (!empty($itemData['to_location'])) {
                            $toLocName = mb_strtoupper(trim($itemData['to_location']), 'UTF-8');
                            $loc = Location::firstOrCreate(['name' => $toLocName]);
                            $bilty->to_location_id = $loc->id;
                        }

                        if (!empty($itemData['consignee_name'])) {
                            $cName = trim($itemData['consignee_name']);
                            $bilty->consignee_name = $cName;
                            $cLedger = AccountLedger::where('ledger_name', $cName)->first();
                            if ($cLedger) {
                                DB::table('parties')->updateOrInsert(
                                    ['id' => $cLedger->id],
                                    ['name' => $cLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                                );
                                $bilty->consignee_id = $cLedger->id;
                            }
                        }

                        if (isset($itemData['weight'])) {
                            $bilty->total_qty = (float)$itemData['weight'];
                        }

                        if (isset($itemData['st_charge'])) {
                            $bilty->st_charge = (float)$itemData['st_charge'];
                        }

                        if (isset($itemData['freight_amount'])) {
                            $bilty->gross_amount = (float)$itemData['freight_amount'];
                        }

                        if (isset($itemData['other_charges'])) {
                            $bilty->rc_charge = (float)$itemData['other_charges'];
                            $bilty->sc_charge = 0;
                        }

                        if (isset($itemData['oda_charge'])) {
                            $bilty->dd_charge = (float)$itemData['oda_charge'];
                        }

                        if (isset($itemData['amount'])) {
                            $rowAmt = (float)$itemData['amount'];
                            $bilty->net_amount = $rowAmt;
                            $paidAmt = (float)($bilty->cash_amount + $bilty->card_amount + $bilty->upi_chq_amount);
                            $bilty->balance_amount = max(0, $rowAmt - $paidAmt);
                        }

                        $bilty->save();

                        // Also update BiltyItem
                        $firstItem = $bilty->items->first();
                        if ($firstItem) {
                            $firstItem->description = $itemData['item_description'] ?? $firstItem->description;
                            $firstItem->invoice_no = $itemData['invoice_no_ref'] ?? $firstItem->invoice_no;
                            $firstItem->no_of_pkgs = (int)($itemData['packages'] ?? $firstItem->no_of_pkgs);
                            $firstItem->weight_val = (float)($itemData['weight'] ?? $firstItem->weight_val);
                            $firstItem->qty = (float)($itemData['weight'] ?? $firstItem->qty);
                            $firstItem->unit = $itemData['weight_type'] ?? $firstItem->unit;
                            $firstItem->rate = (float)($itemData['rate'] ?? $firstItem->rate);
                            $firstItem->st = (float)($itemData['st_charge'] ?? $firstItem->st);
                            $firstItem->rc = (float)($itemData['other_charges'] ?? $firstItem->rc);
                            $firstItem->dd = (float)($itemData['oda_charge'] ?? $firstItem->dd);
                            $firstItem->save();
                        } else {
                            BiltyItem::create([
                                'bilty_id' => $bilty->id,
                                'description' => $itemData['item_description'] ?? 'Goods',
                                'invoice_no' => $itemData['invoice_no_ref'] ?? null,
                                'no_of_pkgs' => (int)($itemData['packages'] ?? 1),
                                'weight_val' => (float)($itemData['weight'] ?? 0),
                                'qty' => (float)($itemData['weight'] ?? 0),
                                'unit' => $itemData['weight_type'] ?? 'KG',
                                'rate' => (float)($itemData['rate'] ?? 0),
                                'st' => (float)($itemData['st_charge'] ?? 0),
                                'rc' => (float)($itemData['other_charges'] ?? 0),
                                'dd' => (float)($itemData['oda_charge'] ?? 0),
                            ]);
                        }
                    }
                }
            }

            // Link any remaining bilties to this invoice
            if (!empty($biltyIds)) {
                Bilty::whereIn('id', $biltyIds)->whereNull('invoice_id')->update(['invoice_id' => $invoice->id]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'series' => $invoice->series,
                    'status' => $invoice->status,
                    'bill_amount' => (float)$invoice->bill_amount,
                    'gst_amount' => (float)$invoice->gst_amount,
                    'total_amount' => (float)$invoice->total_amount,
                    'message' => 'Saved automatically as ' . ucfirst($invoice->status),
                    'update_url' => route('invoice.update', $invoice->id),
                    'edit_url' => route('invoice.edit', $invoice->id),
                ]);
            }

            if ($request->boolean('save_and_print') || $request->filled('save_and_print')) {
                return redirect()->route('invoice.print', $invoice->id);
            }

            $statusLabel = ucfirst($status);
            return redirect()->route('invoice.create')
                ->with('success', "Invoice #{$invoice->series}-{$invoice->invoice_no} saved successfully as {$statusLabel}!")
                ->with('print_invoice_id', $invoice->id);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving Invoice: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Error saving Invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update an existing invoice.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'invoice_no' => 'required',
            'invoice_date' => 'required|date',
            'series' => 'nullable|string',
            'account_name' => 'required|string',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $defaultSeries = $this->getDefaultSeries();
            $series = $request->filled('series') ? strtoupper(trim($request->series)) : ($invoice->series ?: $defaultSeries);
            $invoiceNo = Invoice::parseInvoiceNo($request->invoice_no);

            // Check duplicate excluding current invoice
            $existing = Invoice::where('series', $series)->where('invoice_no', $invoiceNo)->where('id', '!=', $invoice->id)->first();
            if ($existing) {
                DB::rollBack();
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invoice #{$series}-{$invoiceNo} already exists. Please choose a unique Invoice No."
                    ], 422);
                }
                return back()->with('error', "Invoice #{$series}-{$invoiceNo} already exists. Please choose a unique Invoice No.")->withInput();
            }

            $account = AccountLedger::where('ledger_name', $request->account_name)->first();
            $consignor = $request->filled('consignor_name') ? AccountLedger::where('ledger_name', $request->consignor_name)->first() : null;

            $status = $request->input('status', $invoice->status ?: 'finalized');
            if (!in_array($status, ['draft', 'finalized', 'cancelled'])) {
                $status = 'finalized';
            }

            $billAmount = (float)$request->bill_amount;
            $gstPercent = (float)$request->gst_percent;
            $gstAmount = (float)$request->gst_amount;
            $totalAmount = (float)$request->total_amount;

            $invoice->update([
                'series' => $series,
                'invoice_no' => $invoiceNo,
                'invoice_date' => $request->invoice_date,
                'account_id' => $account ? $account->id : null,
                'account_name' => $request->account_name,
                'consignor_id' => $consignor ? $consignor->id : null,
                'consignor_name' => $request->consignor_name,
                'for_month' => $request->for_month,
                'item_filter' => $request->item_filter,
                'is_gst_bill' => $request->boolean('is_gst_bill'),
                'is_igst' => $request->boolean('is_igst'),
                'destination_filter' => $request->destination_filter,
                'unit_filter' => $request->unit_filter,
                'bill_amount' => $billAmount,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'total_amount' => $totalAmount,
                'remark' => $request->remark,
                'status' => $status,
            ]);

            // Track existing attached bilties to release any that were removed
            $oldBiltyIds = $invoice->bilties()->pluck('id')->toArray();
            $newBiltyIds = [];

            // Delete old items and insert fresh
            $invoice->items()->delete();

            foreach ($request->items as $idx => $itemData) {
                $biltyId = !empty($itemData['bilty_id']) ? (int)$itemData['bilty_id'] : null;
                if ($biltyId) {
                    $newBiltyIds[] = $biltyId;
                }

                $biltyDate = null;
                if (!empty($itemData['date'])) {
                    try {
                        $biltyDate = Carbon::parse($itemData['date'])->toDateString();
                    } catch (\Exception $e) {}
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'bilty_id' => $biltyId,
                    'sr_no' => $idx + 1,
                    'bilty_date' => $biltyDate,
                    'bilty_no' => $itemData['bilty_no'] ?? null,
                    'cn_no' => $itemData['cn_no'] ?? null,
                    'packages' => (int)($itemData['packages'] ?? 0),
                    'from_location' => $itemData['from_location'] ?? null,
                    'to_location' => $itemData['to_location'] ?? null,
                    'consignee_name' => $itemData['consignee_name'] ?? null,
                    'item_description' => $itemData['item_description'] ?? null,
                    'invoice_no_ref' => $itemData['invoice_no_ref'] ?? null,
                    'weight' => (float)($itemData['weight'] ?? 0),
                    'weight_type' => $itemData['weight_type'] ?? 'KG',
                    'rate' => (float)($itemData['rate'] ?? 0),
                    'st_charge' => (float)($itemData['st_charge'] ?? 0),
                    'freight_amount' => (float)($itemData['freight_amount'] ?? 0),
                    'unload_rate' => (float)($itemData['unload_rate'] ?? 0),
                    'unload_amount' => (float)($itemData['unload_amount'] ?? 0),
                    'other_charges' => (float)($itemData['other_charges'] ?? 0),
                    'oda_charge' => (float)($itemData['oda_charge'] ?? 0),
                    'amount' => (float)($itemData['amount'] ?? 0),
                ]);

                // Update the original Bilty (C.N.) record if attached
                if ($biltyId) {
                    $bilty = Bilty::with('items')->find($biltyId);
                    if ($bilty) {
                        $bilty->invoice_id = $invoice->id;
                        if ($biltyDate) {
                            $bilty->invoice_date = $biltyDate;
                        }

                        if (isset($itemData['cn_no'])) {
                            $bilty->cn_no = $itemData['cn_no'];
                        }

                        if (isset($itemData['packages'])) {
                            $bilty->total_packages = (int)$itemData['packages'];
                        }

                        if (!empty($itemData['from_location'])) {
                            $fromLocName = mb_strtoupper(trim($itemData['from_location']), 'UTF-8');
                            $loc = Location::firstOrCreate(['name' => $fromLocName]);
                            $bilty->from_location_id = $loc->id;
                        }

                        if (!empty($itemData['to_location'])) {
                            $toLocName = mb_strtoupper(trim($itemData['to_location']), 'UTF-8');
                            $loc = Location::firstOrCreate(['name' => $toLocName]);
                            $bilty->to_location_id = $loc->id;
                        }

                        if (!empty($itemData['consignee_name'])) {
                            $cName = trim($itemData['consignee_name']);
                            $bilty->consignee_name = $cName;
                            $cLedger = AccountLedger::where('ledger_name', $cName)->first();
                            if ($cLedger) {
                                DB::table('parties')->updateOrInsert(
                                    ['id' => $cLedger->id],
                                    ['name' => $cLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                                );
                                $bilty->consignee_id = $cLedger->id;
                            }
                        }

                        if (isset($itemData['weight'])) {
                            $bilty->total_qty = (float)$itemData['weight'];
                        }

                        if (isset($itemData['st_charge'])) {
                            $bilty->st_charge = (float)$itemData['st_charge'];
                        }

                        if (isset($itemData['freight_amount'])) {
                            $bilty->gross_amount = (float)$itemData['freight_amount'];
                        }

                        if (isset($itemData['other_charges'])) {
                            $bilty->rc_charge = (float)$itemData['other_charges'];
                            $bilty->sc_charge = 0;
                        }

                        if (isset($itemData['oda_charge'])) {
                            $bilty->dd_charge = (float)$itemData['oda_charge'];
                        }

                        if (isset($itemData['amount'])) {
                            $rowAmt = (float)$itemData['amount'];
                            $bilty->net_amount = $rowAmt;
                            $paidAmt = (float)($bilty->cash_amount + $bilty->card_amount + $bilty->upi_chq_amount);
                            $bilty->balance_amount = max(0, $rowAmt - $paidAmt);
                        }

                        $bilty->save();
                    }
                }
            }

            // Release removed bilties
            $removedBiltyIds = array_diff($oldBiltyIds, $newBiltyIds);
            if (!empty($removedBiltyIds)) {
                Bilty::whereIn('id', $removedBiltyIds)->update(['invoice_id' => null]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'series' => $invoice->series,
                    'status' => $invoice->status,
                    'bill_amount' => (float)$invoice->bill_amount,
                    'gst_amount' => (float)$invoice->gst_amount,
                    'total_amount' => (float)$invoice->total_amount,
                    'message' => 'Updated automatically as ' . ucfirst($invoice->status),
                    'update_url' => route('invoice.update', $invoice->id),
                    'edit_url' => route('invoice.edit', $invoice->id),
                ]);
            }

            if ($request->boolean('save_and_print') || $request->filled('save_and_print')) {
                return redirect()->route('invoice.print', $invoice->id);
            }

            $statusLabel = ucfirst($status);
            return redirect()->route('invoice.create')
                ->with('success', "Invoice #{$invoice->series}-{$invoice->invoice_no} updated successfully as {$statusLabel}!")
                ->with('print_invoice_id', $invoice->id);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating Invoice: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Error updating Invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cancel an invoice and release its attached bilties.
     */
    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();
        try {
            $invoice->status = 'cancelled';
            $invoice->save();

            // Release attached bilties
            Bilty::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);

            DB::commit();

            return redirect()->route('invoice.register')
                ->with('success', "Invoice #{$invoice->series}-{$invoice->invoice_no} has been CANCELLED and attached consignment notes released.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error cancelling Invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete an invoice and release attached bilties.
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);

        DB::beginTransaction();
        try {
            // Release attached bilties
            Bilty::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);

            // Delete invoice items and invoice
            $invoice->items()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoice.create')
                ->with('success', "Invoice #{$invoice->series}-{$invoice->invoice_no} deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting Invoice: ' . $e->getMessage());
        }
    }

    /**
     * Preview Invoice print layout in-memory without saving to database.
     */
    public function preview(Request $request)
    {
        $account = AccountLedger::where('ledger_name', $request->account_name)->first();
        $consignor = $request->filled('consignor_name') ? AccountLedger::where('ledger_name', $request->consignor_name)->first() : null;

        $invoice = new Invoice([
            'series' => $request->filled('series') ? strtoupper(trim($request->series)) : $this->getDefaultSeries(),
            'invoice_no' => $request->invoice_no ?? 40,
            'invoice_date' => Carbon::parse($request->invoice_date ?? now()),
            'account_id' => $account ? $account->id : null,
            'account_name' => $request->account_name ?? 'ACCOUNT NAME',
            'consignor_id' => $consignor ? $consignor->id : null,
            'consignor_name' => $request->consignor_name,
            'for_month' => $request->for_month,
            'is_gst_bill' => $request->boolean('is_gst_bill'),
            'is_igst' => $request->boolean('is_igst'),
            'bill_amount' => (float)$request->bill_amount,
            'gst_percent' => (float)$request->gst_percent,
            'gst_amount' => (float)$request->gst_amount,
            'total_amount' => (float)$request->total_amount,
            'remark' => $request->remark,
        ]);

        if ($account) {
            $account->load('stateRelation');
            $invoice->setRelation('account', $account);
        }

        $items = collect();
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $idx => $itemData) {
                $biltyDate = null;
                if (!empty($itemData['date'])) {
                    try {
                        $biltyDate = Carbon::parse($itemData['date']);
                    } catch (\Exception $e) {}
                }

                $items->push(new InvoiceItem([
                    'sr_no' => $idx + 1,
                    'bilty_date' => $biltyDate,
                    'bilty_no' => $itemData['bilty_no'] ?? null,
                    'cn_no' => $itemData['cn_no'] ?? null,
                    'packages' => (int)($itemData['packages'] ?? 0),
                    'from_location' => $itemData['from_location'] ?? null,
                    'to_location' => $itemData['to_location'] ?? null,
                    'consignee_name' => $itemData['consignee_name'] ?? null,
                    'item_description' => $itemData['item_description'] ?? null,
                    'invoice_no_ref' => $itemData['invoice_no_ref'] ?? null,
                    'weight' => (float)($itemData['weight'] ?? 0),
                    'weight_type' => $itemData['weight_type'] ?? 'KG',
                    'rate' => (float)($itemData['rate'] ?? 0),
                    'st_charge' => (float)($itemData['st_charge'] ?? 0),
                    'freight_amount' => (float)($itemData['freight_amount'] ?? 0),
                    'unload_rate' => (float)($itemData['unload_rate'] ?? 0),
                    'unload_amount' => (float)($itemData['unload_amount'] ?? 0),
                    'other_charges' => (float)($itemData['other_charges'] ?? 0),
                    'oda_charge' => (float)($itemData['oda_charge'] ?? 0),
                    'amount' => (float)($itemData['amount'] ?? 0),
                ]));
            }
        }
        $invoice->setRelation('items', $items);

        return view('invoice.print', compact('invoice'));
    }

    /**
     * Printable view of Invoice / Party Bill.
     */
    public function print($id)
    {
        $invoice = Invoice::with(['items', 'account.stateRelation', 'consignor', 'user'])->findOrFail($id);
        return view('invoice.print', compact('invoice'));
    }

    /**
     * Build the filtered Invoice query based on request parameters.
     */
    protected function getFilteredInvoicesQuery(Request $request)
    {
        $query = Invoice::with(['account', 'consignor', 'items', 'user']);

        // 1. Party / Account Name (Exact equality =)
        $party = trim($request->input('party', $request->input('account_name', '')));
        if ($party !== '') {
            $query->where('account_name', '=', $party);
        }

        // 2. Series
        if ($request->filled('series')) {
            $query->where('series', trim($request->series));
        }

        // 3. User
        if ($request->filled('user_id') && $request->user_id !== 'all' && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // 4. Mobile No
        if ($request->filled('mobile')) {
            $mobile = trim($request->mobile);
            $query->whereHas('account', function($sq) use ($mobile) {
                $sq->where('mobile', 'like', '%' . $mobile . '%')
                   ->orWhere('phone_o', 'like', '%' . $mobile . '%')
                   ->orWhere('phone_r', 'like', '%' . $mobile . '%');
            });
        }

        // 5. Date Range (From & To)
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // 6. Cancel Status
        $cancelStatus = $request->get('cancel_status', 'non_cancel');
        if ($cancelStatus === 'non_cancel') {
            $query->where(function($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            });
        } elseif ($cancelStatus === 'cancel') {
            $query->where('status', 'cancelled');
        }

        return $query;
    }

    /**
     * Invoice / Party Bill Register Report.
     */
    public function register(Request $request)
    {
        $fromDate = $request->input('from_date', date('Y-m-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        if (!$request->has('from_date')) {
            $request->merge(['from_date' => $fromDate]);
        }
        if (!$request->has('to_date')) {
            $request->merge(['to_date' => $toDate]);
        }

        if ($request->get('export') === 'excel') {
            return $this->exportExcel($request);
        }

        $query = $this->getFilteredInvoicesQuery($request);

        $invoices = $query->orderBy('invoice_date', 'asc')->orderBy('invoice_no', 'asc')->get();

        $parties = AccountLedger::orderBy('ledger_name')->pluck('ledger_name')->filter()->unique()->values();
        $users = User::orderBy('name')->get();

        $totalBillAmt = 0;
        $totalCgstAmt = 0;
        $totalSgstAmt = 0;
        $totalIgstAmt = 0;
        $totalNetAmt = 0;
        $totalDueAmt = 0;

        foreach ($invoices as $inv) {
            $totalBillAmt += (float)$inv->bill_amount;
            $gstAmt = (float)$inv->gst_amount;
            if ($inv->is_igst) {
                $totalIgstAmt += $gstAmt;
            } else {
                $totalCgstAmt += ($gstAmt / 2);
                $totalSgstAmt += ($gstAmt / 2);
            }
            $totalNetAmt += (float)$inv->total_amount;
            $totalDueAmt += (float)$inv->total_amount;
        }

        $defaultSeries = $this->getDefaultSeries();
        $seriesList = \App\Models\Series::orderBy('name', 'asc')->get();

        return view('invoice.register', compact(
            'invoices',
            'parties',
            'users',
            'seriesList',
            'defaultSeries',
            'totalBillAmt',
            'totalCgstAmt',
            'totalSgstAmt',
            'totalIgstAmt',
            'totalNetAmt',
            'totalDueAmt',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Export Party Bill Register to Excel (XLSX).
     */
    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredInvoicesQuery($request);
        $invoices = $query->orderBy('invoice_date', 'asc')->orderBy('invoice_no', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice Register');

        // Company Header
        $sheet->setCellValue('A1', 'OMKAAR LOGISTICS - Invoice Register');
        $sheet->mergeCells('A1:Q1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('8B0000');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $fromDateStr = $request->filled('from_date') ? Carbon::parse($request->from_date)->format('d-m-Y') : 'All';
        $toDateStr = $request->filled('to_date') ? Carbon::parse($request->to_date)->format('d-m-Y') : 'All';
        $sheet->setCellValue('A2', "Date Range: {$fromDateStr} To {$toDateStr}");
        $sheet->mergeCells('A2:Q2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column Headers
        $headers = [
            'A3' => 'Srno.',
            'B3' => 'Series',
            'C3' => 'Invoice No',
            'D3' => 'Invoice Date',
            'E3' => 'Time',
            'F3' => 'Details of Buyer (Billed To)',
            'G3' => 'Address',
            'H3' => 'Mobile',
            'I3' => 'Invoice Amt.',
            'J3' => 'GST%',
            'K3' => 'CGST',
            'L3' => 'SGST',
            'M3' => 'IGST',
            'N3' => 'Net Amt',
            'O3' => 'Due Amt',
            'P3' => 'Remark',
            'Q3' => 'User',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E4E2DE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '999999']],
            ],
        ];
        $sheet->getStyle('A3:Q3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(24);

        $rowNum = 4;
        $srNo = 1;
        $totalBillAmt = 0;
        $totalCgstAmt = 0;
        $totalSgstAmt = 0;
        $totalIgstAmt = 0;
        $totalNetAmt = 0;
        $totalDueAmt = 0;

        foreach ($invoices as $inv) {
            $billAmt = (float)$inv->bill_amount;
            $gstAmt = (float)$inv->gst_amount;
            $cgst = (!$inv->is_igst && $gstAmt > 0) ? ($gstAmt / 2) : 0;
            $sgst = (!$inv->is_igst && $gstAmt > 0) ? ($gstAmt / 2) : 0;
            $igst = ($inv->is_igst && $gstAmt > 0) ? $gstAmt : 0;
            $netAmt = (float)$inv->total_amount;
            $dueAmt = (float)$inv->total_amount;

            $totalBillAmt += $billAmt;
            $totalCgstAmt += $cgst;
            $totalSgstAmt += $sgst;
            $totalIgstAmt += $igst;
            $totalNetAmt += $netAmt;
            $totalDueAmt += $dueAmt;

            $sheet->setCellValue('A' . $rowNum, $srNo++);
            $sheet->setCellValue('B' . $rowNum, $inv->series);
            $sheet->setCellValue('C' . $rowNum, $inv->invoice_no);
            $sheet->setCellValue('D' . $rowNum, $inv->invoice_date ? $inv->invoice_date->format('d-m-Y') : '');
            $sheet->setCellValue('E' . $rowNum, $inv->created_at ? $inv->created_at->format('h:i A') : '');
            $sheet->setCellValue('F' . $rowNum, $inv->account_name);
            $sheet->setCellValue('G' . $rowNum, $inv->account ? $inv->account->address : '');
            $sheet->setCellValue('H' . $rowNum, $inv->account ? ($inv->account->mobile ?: ($inv->account->phone_o ?: '')) : '');
            $sheet->setCellValue('I' . $rowNum, $billAmt);
            $sheet->setCellValue('J' . $rowNum, (float)$inv->gst_percent);
            $sheet->setCellValue('K' . $rowNum, $cgst);
            $sheet->setCellValue('L' . $rowNum, $sgst);
            $sheet->setCellValue('M' . $rowNum, $igst);
            $sheet->setCellValue('N' . $rowNum, $netAmt);
            $sheet->setCellValue('O' . $rowNum, $dueAmt);
            $sheet->setCellValue('P' . $rowNum, $inv->remark ?: '');
            $sheet->setCellValue('Q' . $rowNum, $inv->user ? ($inv->user->name ?: $inv->user->username) : 'ADMIN');

            // Alignment
            $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowNum . ':G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $rowNum . ':O' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('P' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('Q' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Numbers format
            $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('0.00');
            $sheet->getStyle('K' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D0D0');

            if ($inv->status === 'draft') {
                $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEF08A');
            }

            $rowNum++;
        }

        // Totals Row
        $sheet->setCellValue('A' . $rowNum, 'Total :');
        $sheet->mergeCells('A' . $rowNum . ':H' . $rowNum);
        $sheet->setCellValue('I' . $rowNum, $totalBillAmt);
        $sheet->setCellValue('J' . $rowNum, '');
        $sheet->setCellValue('K' . $rowNum, $totalCgstAmt);
        $sheet->setCellValue('L' . $rowNum, $totalSgstAmt);
        $sheet->setCellValue('M' . $rowNum, $totalIgstAmt);
        $sheet->setCellValue('N' . $rowNum, $totalNetAmt);
        $sheet->setCellValue('O' . $rowNum, $totalDueAmt);
        $sheet->setCellValue('P' . $rowNum, '');
        $sheet->setCellValue('Q' . $rowNum, '');

        $totalStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEDBDB'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '999999']],
            ],
        ];
        $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->applyFromArray($totalStyle);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('K' . $rowNum . ':O' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Party_Bill_Register_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
