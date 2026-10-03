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
}
