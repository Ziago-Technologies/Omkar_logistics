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
     * Format numeric invoice number to string e.g. GSTOML2627045
     */
    public static function formatInvoiceNo($num, $series = '26-27'): string
    {
        if ($num === null || $num === '') return '';

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
}
