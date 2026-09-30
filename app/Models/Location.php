<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = ['company_id', 'city_id', 'name'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(CityModel::class, 'city_id');
    }
}

