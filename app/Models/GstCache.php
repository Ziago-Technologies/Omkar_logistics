<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstCache extends Model
{
    protected $table = 'gst_cache';

    protected $fillable = [
        'gstin',
        'legal_name',
        'trade_name',
        'status',
        'taxpayer_type',
        'constitution',
        'address',
        'city',
        'district',
        'state_name',
        'state_code',
        'pincode',
        'pan',
        'source',
        'raw_response',
        'last_checked_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'last_checked_at' => 'datetime',
    ];
}
