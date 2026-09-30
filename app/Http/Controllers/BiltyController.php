<?php

namespace App\Http\Controllers;

use App\Models\AccountLedger;
use App\Models\CityModel;
use App\Models\MeasurementUnit;
use App\Models\Bilty;
use App\Models\BiltyItem;
use App\Models\Location;
use App\Models\Party;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiltyController extends Controller
{
    public function create()
    {
        $locations = CityModel::orderBy('name')->get();
        
        // Load options from account_ledgers
        $consignors = AccountLedger::whereIn('under_group', ['Debtors', 'Creditors'])->orderBy('ledger_name')->get()->map(function($l) {
            return (object)[
                'id' => $l->id,
                'name' => $l->ledger_name
            ];
        });
        $consignees = $consignors;
        $parties = $consignors;

        // Pull transport names under Transport Expense (and transports from Bilties)
        $transportsList = AccountLedger::where('under_group', 'like', '%Transport%')
            ->pluck('ledger_name')
            ->filter()
            ->map(fn($v) => trim($v))
            ->unique()
            ->sort()
            ->values();

        $transportsUpper = $transportsList->map(fn($t) => strtoupper($t))->toArray();

        // Pull vehicles under Vehicle Expense and Oil Expense, merged with distinct vehicle numbers from Bilties (excluding transports)
        $biltyVehicleNos = Bilty::whereNotNull('vehicle_no')
            ->where('vehicle_no', '!=', '')
            ->pluck('vehicle_no')
            ->filter(fn($v) => !in_array(strtoupper(trim($v)), $transportsUpper));

        $vehicleNumbers = AccountLedger::where(function($q) {
                $q->where('under_group', 'like', '%Vehicle%')
                  ->orWhere('under_group', 'like', '%Oil%');
            })
            ->pluck('ledger_name')
            ->concat($biltyVehicleNos)
            ->filter()
            ->map(fn($v) => trim($v))
            ->unique()
            ->sort()
            ->values();

        $vehicles = $vehicleNumbers;

        // Load active Series list
        $seriesList = Series::orderBy('name', 'asc')->get();

        // Determine default series based on top navbar Financial Year session (e.g., '2026-2027' -> '26-27')
        $fySession = session('financial_year', '2026-2027');
        $defaultSeries = '26-27';
        if ($fySession && $fySession !== 'ALL' && strpos($fySession, '-') !== false) {
            $parts = explode('-', $fySession);
            if (count($parts) === 2 && strlen(trim($parts[0])) >= 2 && strlen(trim($parts[1])) >= 2) {
                $defaultSeries = substr(trim($parts[0]), -2) . '-' . substr(trim($parts[1]), -2);
            }
        }

        // Dynamically create or resolve series in Series Master table
        $seriesObj = $this->resolveOrCreateSeries($defaultSeries);

        // Load active Series list
        $seriesList = Series::orderBy('name', 'asc')->get();

        // Calculate next C.N. number for default series scoped to active company
        $maxBiltyNo = Bilty::forCompany()->where(function($q) use ($seriesObj, $defaultSeries) {
            $q->where('series_id', $seriesObj->id)->orWhere('series', $defaultSeries);
        })->max('bilty_no');

        if ($defaultSeries === '26-27') {
            $next = ($maxBiltyNo && $maxBiltyNo >= 4306) ? ($maxBiltyNo + 1) : 4306;
        } else {
            $next = $maxBiltyNo ? ($maxBiltyNo + 1) : 1;
        }
        $nextBiltyNo = str_pad($next, 2, '0', STR_PAD_LEFT);

        // Calculate next Voucher No
        $lastVoucher = Bilty::forCompany()->orderBy('voucher_no', 'desc')->first();
        $nextVoucherNo = $lastVoucher ? ($lastVoucher->voucher_no + 1) : 1795;

        $measurementUnits = MeasurementUnit::forCompany()->active()->orderBy('unit_code')->get();

        return view('bilty.create', compact('locations', 'consignors', 'consignees', 'parties', 'vehicles', 'vehicleNumbers', 'transportsList', 'seriesList', 'defaultSeries', 'nextBiltyNo', 'nextVoucherNo', 'measurementUnits'));
    }

    protected function resolveOrCreateSeries($seriesCode)
    {
        $seriesCode = strtoupper(trim($seriesCode ?: '26-27'));
        return Series::firstOrCreate(
            ['name' => $seriesCode],
            [
                'description' => 'FY ' . (strlen($seriesCode) === 5 ? ('20' . substr($seriesCode, 0, 2) . '-20' . substr($seriesCode, 3, 2)) : $seriesCode),
                'is_active' => true,
            ]
        );
    }

    public function getNextBiltyNo(Request $request)
    {
        $seriesCode = trim($request->query('series', '26-27'));
        $seriesObj = $this->resolveOrCreateSeries($seriesCode);
        $series = $seriesObj->name;

        $maxBiltyNo = Bilty::forCompany()->where(function($q) use ($seriesObj, $series) {
            $q->where('series_id', $seriesObj->id)->orWhere('series', $series);
        })->max('bilty_no');
        
        if ($series === '26-27') {
            $next = ($maxBiltyNo && $maxBiltyNo >= 4306) ? ($maxBiltyNo + 1) : 4306;
        } else {
            $next = $maxBiltyNo ? ($maxBiltyNo + 1) : 1;
        }

        return response()->json([
            'next_bilty_no' => str_pad($next, 2, '0', STR_PAD_LEFT)
        ]);
    }

    public function getPartyDetails($id)
    {
        $party = AccountLedger::find($id);
        if (!$party) {
            return response()->json(['error' => 'Party not found'], 404);
        }
        // Normalize fields for Bilty Javascript mapping expects name, mobile, gstin, address
        return response()->json([
            'id' => $party->id,
            'name' => $party->ledger_name,
            'mobile' => $party->mobile ?: $party->phone_o ?: $party->phone_r,
            'gstin' => $party->gst_no,
            'address' => $party->address
        ]);
    }

    public function store(Request $request)
    {
        $isDraft = ($request->input('status') === 'draft');

        if ($isDraft) {
            $request->validate([
                'series' => 'nullable|string|max:5',
                'bilty_no' => 'required|integer',
                'invoice_date' => 'nullable|date',
                'from_location_id' => 'nullable',
                'to_location_id' => 'nullable',
                'consignor_id' => 'nullable',
                'consignee_id' => 'nullable',
                'billing_type' => 'nullable|string|max:50',
                'billing_party_id' => 'nullable',
                'items' => 'nullable|array',
            ]);
        } else {
            $request->validate([
                'series' => 'nullable|string|max:5',
                'bilty_no' => 'required|integer',
                'invoice_date' => 'required|date',
                'from_location_id' => 'nullable',
                'from_location_text' => 'required_without:from_location_id|nullable|string',
                'to_location_id' => 'nullable',
                'to_location_text' => 'required_without:to_location_id|nullable|string',
                'consignor_id' => 'nullable',
                'consignor_name' => 'required_without:consignor_id|nullable|string',
                'consignee_id' => 'nullable',
                'consignee_name' => 'required_without:consignee_id|nullable|string',
                'billing_type' => 'required|string|in:Paid,To Pay,T.B.B.',
                'billing_party_id' => 'nullable',
                'vehicle_no' => 'nullable|string|max:50',
                'eway_bill_no' => 'nullable|string|max:50',
                'cn_no' => 'nullable|string|max:50',
                'shipping_status' => 'nullable|string|max:50',
                
                // Grid items
                'items' => 'required|array|min:1',
                'items.*.no_of_pkgs' => 'required|integer|min:1',
                'items.*.packing' => 'nullable|string|max:100',
                'items.*.description' => 'nullable|string|max:255',
                'items.*.invoice_no' => 'nullable|string|max:50',
                'items.*.invoice_value' => 'nullable|numeric|min:0',
                'items.*.unit' => 'required|string|max:50',
                'items.*.weight_val' => 'nullable|numeric|min:0',
                'items.*.qty' => 'required|numeric|min:0',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.st' => 'nullable|numeric|min:0',
                'items.*.rc' => 'nullable|numeric|min:0',
                'items.*.sc' => 'nullable|numeric|min:0',
                'items.*.dd' => 'nullable|numeric|min:0',

                // Summary Totals
                'total_packages' => 'required|integer',
                'total_qty' => 'required|numeric',
                'gross_amount' => 'required|numeric',
                'st_charge' => 'nullable|numeric',
                'rc_charge' => 'nullable|numeric',
                'sc_charge' => 'nullable|numeric',
                'dd_charge' => 'nullable|numeric',
                'round_off' => 'nullable|numeric',
                'net_amount' => 'required|numeric',

                // Payments
                'cash_amount' => 'nullable|numeric',
                'card_amount' => 'nullable|numeric',
                'upi_chq_amount' => 'nullable|numeric',
                'ref_no' => 'nullable|string|max:50',
                'payment_date' => 'nullable|date',
                'bank_account' => 'nullable|string|max:100',
                'balance_amount' => 'required|numeric',
                'remark' => 'nullable|string',
                'voucher_no' => 'nullable|integer',
            ]);
        }

        try {
            DB::beginTransaction();

            $fromLoc = null;
            if ($request->filled('from_location_id')) {
                $fromCity = CityModel::find($request->from_location_id);
                if ($fromCity) {
                    $fromLoc = Location::firstOrCreate(['name' => mb_strtoupper($fromCity->name, 'UTF-8')]);
                }
            } elseif ($request->filled('from_location_text')) {
                $fromLoc = Location::firstOrCreate(['name' => mb_strtoupper(trim($request->from_location_text), 'UTF-8')]);
            }

            $toLoc = null;
            if ($request->filled('to_location_id')) {
                $toCity = CityModel::find($request->to_location_id);
                if ($toCity) {
                    $toLoc = Location::firstOrCreate(['name' => mb_strtoupper($toCity->name, 'UTF-8')]);
                }
            } elseif ($request->filled('to_location_text')) {
                $toLoc = Location::firstOrCreate(['name' => mb_strtoupper(trim($request->to_location_text), 'UTF-8')]);
            }

            $consignorLedger = null;
            if ($request->filled('consignor_id')) {
                $consignorLedger = AccountLedger::find($request->consignor_id);
                if ($consignorLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $consignorLedger->id],
                        ['name' => $consignorLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            $consigneeLedger = null;
            if ($request->filled('consignee_id')) {
                $consigneeLedger = AccountLedger::find($request->consignee_id);
                if ($consigneeLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $consigneeLedger->id],
                        ['name' => $consigneeLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            $billingLedger = null;
            if ($request->filled('billing_party_id')) {
                $billingLedger = AccountLedger::find($request->billing_party_id);
                if ($billingLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $billingLedger->id],
                        ['name' => $billingLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Create Bilty Header
            $seriesObj = $this->resolveOrCreateSeries($request->series ?? '26-27');
            $bilty = Bilty::create([
                'company_id' => session('company_id', 1),
                'series_id' => $seriesObj->id,
                'series' => $seriesObj->name,
                'bilty_no' => $request->bilty_no,
                'invoice_date' => $request->invoice_date ?: now()->toDateString(),
                'from_location_id' => $fromLoc ? $fromLoc->id : null,
                'to_location_id' => $toLoc ? $toLoc->id : null,
                'from_city_id' => isset($fromCity) && $fromCity ? $fromCity->id : ($fromLoc ? $fromLoc->city_id : null),
                'to_city_id' => isset($toCity) && $toCity ? $toCity->id : ($toLoc ? $toLoc->city_id : null),
                'from_state_id' => isset($fromCity) && $fromCity ? $fromCity->state_id : null,
                'to_state_id' => isset($toCity) && $toCity ? $toCity->state_id : null,
                'from_country_id' => 1,
                'to_country_id' => 1,
                'consignor_id' => $consignorLedger ? $consignorLedger->id : null,
                'consignor_name' => $this->formatUpper($request->consignor_name ?: ($consignorLedger ? $consignorLedger->ledger_name : null)),
                'consignor_mobile' => $request->filled('consignor_mobile') ? $this->formatUpper($request->consignor_mobile) : ($consignorLedger ? $this->formatUpper($consignorLedger->mobile ?: $consignorLedger->phone_o) : null),
                'consignee_id' => $consigneeLedger ? $consigneeLedger->id : null,
                'consignee_name' => $this->formatUpper($request->consignee_name ?: ($consigneeLedger ? $consigneeLedger->ledger_name : null)),
                'consignee_mobile' => $request->filled('consignee_mobile') ? $this->formatUpper($request->consignee_mobile) : ($consigneeLedger ? $this->formatUpper($consigneeLedger->mobile ?: $consigneeLedger->phone_o) : null),
                'billing_type' => $this->formatUpper($request->billing_type ?: 'Paid'),
                'type' => $this->formatUpper($request->vehicle_type ?? 'Vehicle Number'),
                'billing_party_id' => $billingLedger ? $billingLedger->id : null,
                'billing_party_name' => $this->formatUpper($request->billing_party_name ?: ($billingLedger ? $billingLedger->ledger_name : null)),
                'cn_no' => $this->formatUpper($request->cn_no),
                'vehicle_no' => $this->formatUpper($request->vehicle_no),
                'shipping_status' => $request->filled('shipping_status') ? trim($request->shipping_status) : ($request->filled('vehicle_no') ? (strtoupper(trim($request->vehicle_type ?? '')) === 'TRANSPORT NAME' ? 'Shipped' : 'In Transit') : 'Booked'),
                'eway_bill_no' => $this->formatUpper($request->eway_bill_no),
                
                'total_packages' => $request->total_packages ?? 0,
                'total_qty' => $request->total_qty ?? 0.000,
                'gross_amount' => $request->gross_amount ?? 0.00,
                
                'st_charge' => $request->st_charge ?? 0.00,
                'rc_charge' => $request->rc_charge ?? 0.00,
                'sc_charge' => $request->sc_charge ?? 0.00,
                'dd_charge' => $request->dd_charge ?? 0.00,
                'round_off' => $request->round_off ?? 0.00,
                'net_amount' => $request->net_amount ?? 0.00,
                
                'cash_amount' => $request->cash_amount ?? 0.00,
                'card_amount' => $request->card_amount ?? 0.00,
                'upi_chq_amount' => $request->upi_chq_amount ?? 0.00,
                'ref_no' => $this->formatUpper($request->ref_no),
                'payment_date' => $request->payment_date,
                'bank_account' => $this->formatUpper($request->bank_account),
                'balance_amount' => $request->balance_amount ?? 0.00,
                'remark' => $this->formatUpper($request->remark),
                'voucher_no' => $request->voucher_no,
                'status' => $isDraft ? 'draft' : 'final',
                'user_id' => auth()->id(),
            ]);

            // Save Bilty Items
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    BiltyItem::create([
                        'bilty_id' => $bilty->id,
                        'no_of_pkgs' => $itemData['no_of_pkgs'] ?? 0,
                        'packing' => $this->formatUpper($itemData['packing'] ?? ''),
                        'description' => $this->formatUpper($itemData['description'] ?? ''),
                        'invoice_no' => $this->formatUpper($itemData['invoice_no'] ?? ''),
                        'invoice_value' => $itemData['invoice_value'] ?? 0.00,
                        'unit' => $this->formatUpper($itemData['unit'] ?? 'KG'),
                        'weight_val' => $itemData['weight_val'] ?? 0.000,
                        'qty' => $itemData['qty'] ?? 0.000,
                        'rate' => $itemData['rate'] ?? 0.00,
                        'st' => $itemData['st'] ?? 0.00,
                        'rc' => $itemData['rc'] ?? 0.00,
                        'sc' => $itemData['sc'] ?? 0.00,
                        'dd' => $itemData['dd'] ?? 0.00,
                    ]);
                }
            }

            DB::commit();

            if ($request->has('print_after_save') && !$isDraft) {
                return redirect()->route('bilty.print', $bilty->id);
            }

            $successMsg = $isDraft 
                ? ('Bilty #' . $bilty->bilty_no . ' saved as draft successfully!')
                : ('Bilty #' . $bilty->bilty_no . ' saved successfully!');

            return redirect()->route('bilty.create')
                ->with('success', $successMsg)
                ->with('print_id', $bilty->id);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1062) {
                // Intercept Duplicate Entry SQLSTATE code
                $errorMessage = "Duplicate Entry: A consignment note (Bilty) with the number '" . $request->bilty_no . "' already exists for series '" . ($request->series ?? '26-27') . "'. Please enter a unique Bilty number.";
            } else {
                $errorMessage = 'Database Error: ' . $e->getMessage();
            }
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to save Bilty: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function lookup($bilty_no)
    {
        // Try to find the bilty scoped by company
        $bilty = Bilty::forCompany()->with(['items', 'user', 'consignor', 'consignee', 'billingParty', 'fromLocation', 'toLocation'])->where('bilty_no', $bilty_no)->first();
        if (!$bilty) {
            return response()->json(['error' => 'Bilty consignment not found'], 404);
        }

        // Map model fields to City IDs
        $fromLocationName = DB::table('locations')->where('id', $bilty->from_location_id)->value('name');
        $toLocationName = DB::table('locations')->where('id', $bilty->to_location_id)->value('name');

        $fromCity = DB::table('cities')->where('name', $fromLocationName)->first();
        if (!$fromCity && $fromLocationName) {
            $fromCity = DB::table('cities')->where('name', 'like', $fromLocationName)->first();
        }

        $toCity = DB::table('cities')->where('name', $toLocationName)->first();
        if (!$toCity && $toLocationName) {
            $toCity = DB::table('cities')->where('name', 'like', $toLocationName)->first();
        }

        return response()->json([
            'bilty' => $bilty,
            'from_city_id' => $fromCity ? $fromCity->id : null,
            'to_city_id' => $toCity ? $toCity->id : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $bilty = Bilty::findOrFail($id);
        $isDraft = ($request->input('status') === 'draft');

        if ($isDraft) {
            $request->validate([
                'series' => 'nullable|string|max:5',
                'bilty_no' => 'required|integer',
                'invoice_date' => 'nullable|date',
                'from_location_id' => 'nullable',
                'to_location_id' => 'nullable',
                'consignor_id' => 'nullable',
                'consignee_id' => 'nullable',
                'billing_type' => 'nullable|string|max:50',
                'billing_party_id' => 'nullable',
                'items' => 'nullable|array',
            ]);
        } else {
            $request->validate([
                'series' => 'nullable|string|max:5',
                'bilty_no' => 'required|integer',
                'invoice_date' => 'required|date',
                'from_location_id' => 'nullable',
                'from_location_text' => 'required_without:from_location_id|nullable|string',
                'to_location_id' => 'nullable',
                'to_location_text' => 'required_without:to_location_id|nullable|string',
                'consignor_id' => 'nullable',
                'consignor_name' => 'required_without:consignor_id|nullable|string',
                'consignee_id' => 'nullable',
                'consignee_name' => 'required_without:consignee_id|nullable|string',
                'billing_type' => 'required|string|in:Paid,To Pay,T.B.B.',
                'billing_party_id' => 'nullable',
                'vehicle_no' => 'nullable|string|max:50',
                'eway_bill_no' => 'nullable|string|max:50',
                'cn_no' => 'nullable|string|max:50',
                
                // Grid items
                'items' => 'required|array|min:1',
                'items.*.no_of_pkgs' => 'required|integer|min:1',
                'items.*.packing' => 'nullable|string|max:100',
                'items.*.description' => 'nullable|string|max:255',
                'items.*.invoice_no' => 'nullable|string|max:50',
                'items.*.invoice_value' => 'nullable|numeric|min:0',
                'items.*.unit' => 'required|string|max:50',
                'items.*.weight_val' => 'nullable|numeric|min:0',
                'items.*.qty' => 'required|numeric|min:0',
                'items.*.rate' => 'required|numeric|min:0',
                'items.*.st' => 'nullable|numeric|min:0',
                'items.*.rc' => 'nullable|numeric|min:0',
                'items.*.sc' => 'nullable|numeric|min:0',
                'items.*.dd' => 'nullable|numeric|min:0',

                // Summary Totals
                'total_packages' => 'required|integer',
                'total_qty' => 'required|numeric',
                'gross_amount' => 'required|numeric',
                'st_charge' => 'nullable|numeric',
                'rc_charge' => 'nullable|numeric',
                'sc_charge' => 'nullable|numeric',
                'dd_charge' => 'nullable|numeric',
                'round_off' => 'nullable|numeric',
                'net_amount' => 'required|numeric',

                // Payments
                'cash_amount' => 'nullable|numeric',
                'card_amount' => 'nullable|numeric',
                'upi_chq_amount' => 'nullable|numeric',
                'ref_no' => 'nullable|string|max:50',
                'payment_date' => 'nullable|date',
                'bank_account' => 'nullable|string|max:100',
                'balance_amount' => 'required|numeric',
                'remark' => 'nullable|string',
                'voucher_no' => 'nullable|integer',
            ]);
        }

        try {
            DB::beginTransaction();

            $fromLoc = null;
            if ($request->filled('from_location_id')) {
                $fromCity = CityModel::find($request->from_location_id);
                if ($fromCity) {
                    $fromLoc = Location::firstOrCreate(['name' => mb_strtoupper($fromCity->name, 'UTF-8')]);
                }
            } elseif ($request->filled('from_location_text')) {
                $fromLoc = Location::firstOrCreate(['name' => mb_strtoupper(trim($request->from_location_text), 'UTF-8')]);
            }

            $toLoc = null;
            if ($request->filled('to_location_id')) {
                $toCity = CityModel::find($request->to_location_id);
                if ($toCity) {
                    $toLoc = Location::firstOrCreate(['name' => mb_strtoupper($toCity->name, 'UTF-8')]);
                }
            } elseif ($request->filled('to_location_text')) {
                $toLoc = Location::firstOrCreate(['name' => mb_strtoupper(trim($request->to_location_text), 'UTF-8')]);
            }

            $consignorLedger = null;
            if ($request->filled('consignor_id')) {
                $consignorLedger = AccountLedger::find($request->consignor_id);
                if ($consignorLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $consignorLedger->id],
                        ['name' => $consignorLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            $consigneeLedger = null;
            if ($request->filled('consignee_id')) {
                $consigneeLedger = AccountLedger::find($request->consignee_id);
                if ($consigneeLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $consigneeLedger->id],
                        ['name' => $consigneeLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            $billingLedger = null;
            if ($request->filled('billing_party_id')) {
                $billingLedger = AccountLedger::find($request->billing_party_id);
                if ($billingLedger) {
                    DB::table('parties')->updateOrInsert(
                        ['id' => $billingLedger->id],
                        ['name' => $billingLedger->ledger_name, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }

            // Update Header
            $seriesObj = $this->resolveOrCreateSeries($request->series ?? $bilty->series);
            $bilty->update([
                'series_id' => $seriesObj->id,
                'series' => $seriesObj->name,
                'bilty_no' => $request->bilty_no,
                'invoice_date' => $request->invoice_date ?: ($bilty->invoice_date ?: now()->toDateString()),
                'from_location_id' => $fromLoc ? $fromLoc->id : $bilty->from_location_id,
                'to_location_id' => $toLoc ? $toLoc->id : $bilty->to_location_id,
                'from_city_id' => isset($fromCity) && $fromCity ? $fromCity->id : ($fromLoc ? $fromLoc->city_id : $bilty->from_city_id),
                'to_city_id' => isset($toCity) && $toCity ? $toCity->id : ($toLoc ? $toLoc->city_id : $bilty->to_city_id),
                'from_state_id' => isset($fromCity) && $fromCity ? $fromCity->state_id : $bilty->from_state_id,
                'to_state_id' => isset($toCity) && $toCity ? $toCity->state_id : $bilty->to_state_id,
                'from_country_id' => $bilty->from_country_id ?: 1,
                'to_country_id' => $bilty->to_country_id ?: 1,
                'consignor_id' => $consignorLedger ? $consignorLedger->id : $bilty->consignor_id,
                'consignor_name' => $this->formatUpper($request->consignor_name ?: ($consignorLedger ? $consignorLedger->ledger_name : $bilty->consignor_name)),
                'consignor_mobile' => $request->filled('consignor_mobile') ? $this->formatUpper($request->consignor_mobile) : ($consignorLedger ? $this->formatUpper($consignorLedger->mobile ?: $consignorLedger->phone_o) : $bilty->consignor_mobile),
                'consignee_id' => $consigneeLedger ? $consigneeLedger->id : $bilty->consignee_id,
                'consignee_name' => $this->formatUpper($request->consignee_name ?: ($consigneeLedger ? $consigneeLedger->ledger_name : $bilty->consignee_name)),
                'consignee_mobile' => $request->filled('consignee_mobile') ? $this->formatUpper($request->consignee_mobile) : ($consigneeLedger ? $this->formatUpper($consigneeLedger->mobile ?: $consigneeLedger->phone_o) : $bilty->consignee_mobile),
                'billing_type' => $this->formatUpper($request->billing_type ?: $bilty->billing_type),
                'type' => $this->formatUpper($request->vehicle_type ?? ($bilty->type ?? 'Vehicle Number')),
                'billing_party_id' => $billingLedger ? $billingLedger->id : $request->billing_party_id,
                'billing_party_name' => $this->formatUpper($request->billing_party_name ?: ($billingLedger ? $billingLedger->ledger_name : $bilty->billing_party_name)),
                'cn_no' => $this->formatUpper($request->cn_no),
                'vehicle_no' => $this->formatUpper($request->vehicle_no),
                'shipping_status' => $request->filled('shipping_status') ? trim($request->shipping_status) : ($request->filled('vehicle_no') ? (strtoupper(trim($request->vehicle_type ?? ($bilty->type ?? ''))) === 'TRANSPORT NAME' ? 'Shipped' : 'In Transit') : ($bilty->shipping_status ?: 'Booked')),
                'eway_bill_no' => $this->formatUpper($request->eway_bill_no),
                
                'total_packages' => $request->total_packages ?? $bilty->total_packages,
                'total_qty' => $request->total_qty ?? $bilty->total_qty,
                'gross_amount' => $request->gross_amount ?? $bilty->gross_amount,
                
                'st_charge' => $request->st_charge ?? 0.00,
                'rc_charge' => $request->rc_charge ?? 0.00,
                'sc_charge' => $request->sc_charge ?? 0.00,
                'dd_charge' => $request->dd_charge ?? 0.00,
                'round_off' => $request->round_off ?? 0.00,
                'net_amount' => $request->net_amount ?? $bilty->net_amount,
                
                'cash_amount' => $request->cash_amount ?? 0.00,
                'card_amount' => $request->card_amount ?? 0.00,
                'upi_chq_amount' => $request->upi_chq_amount ?? 0.00,
                'ref_no' => $this->formatUpper($request->ref_no),
                'payment_date' => $request->payment_date,
                'bank_account' => $this->formatUpper($request->bank_account),
                'balance_amount' => $request->balance_amount ?? $bilty->balance_amount,
                'remark' => $this->formatUpper($request->remark),
                'voucher_no' => $request->voucher_no,
                'status' => $isDraft ? 'draft' : 'final',
                'user_id' => $bilty->user_id ?: auth()->id(),
            ]);

            // Clear old items and write new ones
            if ($request->has('items') && is_array($request->items)) {
                BiltyItem::where('bilty_id', $bilty->id)->delete();

                foreach ($request->items as $itemData) {
                    BiltyItem::create([
                        'bilty_id' => $bilty->id,
                        'no_of_pkgs' => $itemData['no_of_pkgs'] ?? 0,
                        'packing' => $this->formatUpper($itemData['packing'] ?? ''),
                        'description' => $this->formatUpper($itemData['description'] ?? ''),
                        'invoice_no' => $this->formatUpper($itemData['invoice_no'] ?? ''),
                        'invoice_value' => $itemData['invoice_value'] ?? 0.00,
                        'unit' => $this->formatUpper($itemData['unit'] ?? 'KG'),
                        'weight_val' => $itemData['weight_val'] ?? 0.000,
                        'qty' => $itemData['qty'] ?? 0.000,
                        'rate' => $itemData['rate'] ?? 0.00,
                        'st' => $itemData['st'] ?? 0.00,
                        'rc' => $itemData['rc'] ?? 0.00,
                        'sc' => $itemData['sc'] ?? 0.00,
                        'dd' => $itemData['dd'] ?? 0.00,
                    ]);
                }
            }

            DB::commit();

            if ($request->has('print_after_save') && !$isDraft) {
                return redirect()->route('bilty.print', $bilty->id);
            }

            $successMsg = $isDraft 
                ? ('Bilty #' . $bilty->bilty_no . ' updated as draft!')
                : ('Bilty #' . $bilty->bilty_no . ' updated successfully!');

            return redirect()->route('bilty.create')
                ->with('success', $successMsg)
                ->with('print_id', $bilty->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Failed to update Bilty: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function formatUpper($value)
    {
        if (is_string($value) && $value !== '') {
            return mb_strtoupper(trim($value), 'UTF-8');
        }
        return $value;
    }

    public function print($id)
    {
        $bilty = Bilty::with(['fromLocation', 'toLocation', 'consignor', 'consignee', 'billingParty', 'items', 'user'])->findOrFail($id);
        return view('bilty.print', compact('bilty'));
    }

    public function downloadPdf(Request $request, $id)
    {
        $bilty = Bilty::with(['fromLocation', 'toLocation', 'consignor', 'consignee', 'billingParty', 'items', 'user'])->findOrFail($id);
        $isPdf = true;
        $html = view('bilty.print', compact('bilty', 'isPdf'))->render();

        $logoPath = public_path('assets/logo.jpg');
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
            $html = preg_replace('/src="[^"]*assets\/logo\.jpg"/', 'src="' . $logoBase64 . '"', $html);
        }

        $tempDir = storage_path('app/pdf_temp');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }
        $fontDir = storage_path('fonts');
        if (!file_exists($fontDir)) {
            @mkdir($fontDir, 0777, true);
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', 96);
        $options->set('tempDir', $tempDir);
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontDir);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('a5', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $seriesPart = $bilty->series ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $bilty->series) . '_' : '';
        $cleanBiltyNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $bilty->bilty_no);
        $filename = 'CN_' . $seriesPart . $cleanBiltyNo . '.pdf';

        $disposition = $request->has('download') ? 'attachment' : 'inline';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }
}
