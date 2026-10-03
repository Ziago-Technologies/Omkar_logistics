<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'series',
        'invoice_no',
        'invoice_date',
        'account_id',
        'account_name',
        'consignor_id',
        'consignor_name',
        'for_month',
        'item_filter',
        'is_gst_bill',
        'is_igst',
        'destination_filter',
        'unit_filter',
        'bill_amount',
        'gst_percent',
        'gst_amount',
        'total_amount',
        'remark',
        'status',
        'user_id'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'is_gst_bill' => 'boolean',
        'is_igst' => 'boolean',
        'bill_amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'account_id');
    }

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'consignor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function bilties(): HasMany
    {
        return $this->hasMany(Bilty::class, 'invoice_id');
    }

    /**
     * Dynamically determine the active financial year series string e.g. '26-27', '27-28'.
     */
    public static function getCurrentSeries($date = null): string
    {
        if ($date) {
            $c = \Carbon\Carbon::parse($date);
            $year = $c->year;
            $month = $c->month;
            $startYear = ($month < 4) ? ($year - 1) : $year;
            $endYear = $startYear + 1;
            return sprintf('%02d-%02d', $startYear % 100, $endYear % 100);
        }

        if (function_exists('session') && session()->has('financial_year')) {
            $fySession = session('financial_year');
            if ($fySession && $fySession !== 'ALL' && strpos($fySession, '-') !== false) {
                $parts = explode('-', $fySession);
                if (count($parts) === 2 && strlen(trim($parts[0])) >= 2 && strlen(trim($parts[1])) >= 2) {
                    return substr(trim($parts[0]), -2) . '-' . substr(trim($parts[1]), -2);
                }
            }
        }

        $now = \Carbon\Carbon::now();
        $year = $now->year;
        $month = $now->month;
        $startYear = ($month < 4) ? ($year - 1) : $year;
        $endYear = $startYear + 1;
        return sprintf('%02d-%02d', $startYear % 100, $endYear % 100);
    }

    /**
     * Format numeric invoice number to string e.g. GSTOML2627045
     */
    public static function formatInvoiceNo($num, $series = null): string
    {
        if ($num === null || $num === '') return '';

        if (empty($series)) {
            $series = static::getCurrentSeries();
        }

        $prefix = 'GSTOML';
        $cleanSeries = '2627';

        if (!empty($series)) {
            $s = trim($series);
            if (preg_match('/^(\d{2})[^\d]*(\d{2})$/', $s, $m)) {
                $cleanSeries = $m[1] . $m[2];
            } else {
                $cleanSeries = preg_replace('/[^0-9A-Za-z]/', '', $s);
            }
        }

        if (!is_numeric($num)) {
            $num = static::parseInvoiceNo($num);
        }

        $paddedNum = str_pad((string)(int)$num, 3, '0', STR_PAD_LEFT);
        return $prefix . $cleanSeries . $paddedNum;
    }

    /**
     * Parse integer invoice_no from user input (e.g. GSTOML2627045 -> 45, "045" -> 45, 45 -> 45)
     */
    public static function parseInvoiceNo($input): int
    {
        if (is_numeric($input)) {
            return (int)$input;
        }
        $str = trim((string)$input);
        if (preg_match('/^(?:GSTOML)?[0-9]{4}([0-9]+)$/i', $str, $matches)) {
            return (int)$matches[1];
        }
        if (preg_match('/(\d+)$/', $str, $matches)) {
            return (int)$matches[1];
        }
        return (int)preg_replace('/[^0-9]/', '', $str);
    }

    /**
     * Accessor for formatted invoice number e.g. GSTOML2627045
     */
    public function getFormattedInvoiceNoAttribute(): string
    {
        return static::formatInvoiceNo($this->invoice_no, $this->series);
    }

    /**
     * Find the next available/unused integer invoice_no for the given series.
     */
    public static function getNextAvailableInvoiceNo($series = null): int
    {
        if (empty($series)) {
            $series = static::getCurrentSeries();
        }

        $usedNumbers = static::where('series', $series)->pluck('invoice_no')->toArray();
        $usedMap = array_flip($usedNumbers);

        $maxUsed = !empty($usedNumbers) ? max($usedNumbers) : 39;
        $candidate = ($maxUsed >= 40) ? ($maxUsed + 1) : 40;

        while (isset($usedMap[$candidate])) {
            $candidate++;
        }

        return $candidate;
    }
}
