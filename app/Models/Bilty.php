<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bilty extends Model
{
    protected $fillable = [
        'company_id',
        'series_id',
        'series',
        'bilty_no',
        'invoice_date',
        'from_location_id',
        'to_location_id',
        'from_city_id',
        'to_city_id',
        'from_state_id',
        'to_state_id',
        'from_country_id',
        'to_country_id',
        'consignor_id',
        'consignor_name',
        'consignor_mobile',
        'consignee_id',
        'consignee_name',
        'consignee_mobile',
        'billing_type',
        'type',
        'billing_party_id',
        'billing_party_name',
        'cn_no',
        'vehicle_no',
        'shipping_status',
        'eway_bill_no',
        'total_packages',
        'total_qty',
        'gross_amount',
        'st_charge',
        'rc_charge',
        'sc_charge',
        'dd_charge',
        'round_off',
        'net_amount',
        'cash_amount',
        'card_amount',
        'upi_chq_amount',
        'ref_no',
        'payment_date',
        'bank_account',
        'balance_amount',
        'remark',
        'voucher_no',
        'status',
        'user_id'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function seriesModel(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: session('company_id');
        if ($companyId) {
            return $query->where('company_id', $companyId);
        }
        return $query;
    }

    public function scopeTbb($query)
    {
        return $query->where(function($q) {
            $q->whereIn('billing_type', ['T.B.B.', 'TBB', 'T.B.B', 'To Be Billed', 'TO BE BILLED', 't.b.b.', 'tbb'])
              ->orWhere(\Illuminate\Support\Facades\DB::raw("REPLACE(REPLACE(UPPER(COALESCE(billing_type, '')), '.', ''), ' ', '')"), 'TBB')
              ->orWhere(\Illuminate\Support\Facades\DB::raw("REPLACE(REPLACE(UPPER(COALESCE(billing_type, '')), '.', ''), ' ', '')"), 'TOBEBILLED');
        });
    }

    protected $casts = [
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'total_packages' => 'integer',
        'total_qty' => 'decimal:3',
        'gross_amount' => 'decimal:2',
        'st_charge' => 'decimal:2',
        'rc_charge' => 'decimal:2',
        'sc_charge' => 'decimal:2',
        'dd_charge' => 'decimal:2',
        'round_off' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'card_amount' => 'decimal:2',
        'upi_chq_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2'
    ];

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(CityModel::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(CityModel::class, 'to_city_id');
    }

    public function fromState(): BelongsTo
    {
        return $this->belongsTo(StateModel::class, 'from_state_id');
    }

    public function toState(): BelongsTo
    {
        return $this->belongsTo(StateModel::class, 'to_state_id');
    }

    public function fromCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'from_country_id');
    }

    public function toCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'to_country_id');
    }

    public function getFromLocationNameAttribute()
    {
        if ($this->fromCity) {
            return $this->fromCity->name;
        }
        if ($this->fromLocation) {
            return $this->fromLocation->name;
        }
        return '';
    }

    public function getToLocationNameAttribute()
    {
        if ($this->toCity) {
            return $this->toCity->name;
        }
        if ($this->toLocation) {
            return $this->toLocation->name;
        }
        return '';
    }

    public function consignor(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'consignor_id');
    }

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'consignee_id');
    }

    public function billingParty(): BelongsTo
    {
        return $this->belongsTo(AccountLedger::class, 'billing_party_id');
    }

    public function getConsignorNameAttribute()
    {
        if (!empty($this->attributes['consignor_name'])) {
            return $this->attributes['consignor_name'];
        }
        if ($this->consignor) {
            return $this->consignor->ledger_name ?? $this->consignor->name;
        }
        return '';
    }

    public function getConsigneeNameAttribute()
    {
        if (!empty($this->attributes['consignee_name'])) {
            return $this->attributes['consignee_name'];
        }
        if ($this->consignee) {
            return $this->consignee->ledger_name ?? $this->consignee->name;
        }
        return '';
    }

    public function getBillingPartyNameAttribute()
    {
        if (!empty($this->attributes['billing_party_name'])) {
            return $this->attributes['billing_party_name'];
        }
        if ($this->billingParty) {
            return $this->billingParty->ledger_name ?? $this->billingParty->name;
        }
        return '';
    }

    public function getConsignorMobileAttribute()
    {
        if (!empty($this->attributes['consignor_mobile'])) {
            return $this->attributes['consignor_mobile'];
        }
        if ($this->consignor) {
            return $this->consignor->mobile ?: ($this->consignor->phone_o ?: $this->consignor->phone_r);
        }
        return '';
    }

    public function getConsigneeMobileAttribute()
    {
        if (!empty($this->attributes['consignee_mobile'])) {
            return $this->attributes['consignee_mobile'];
        }
        if ($this->consignee) {
            return $this->consignee->mobile ?: ($this->consignee->phone_o ?: $this->consignee->phone_r);
        }
        return '';
    }

    public function items(): HasMany
    {
        return $this->hasMany(BiltyItem::class, 'bilty_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
