<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CityModel extends Model
{
    protected $table = 'cities';
    protected $fillable = ['state_id', 'name', 'short_name'];

    public function stateRelation()
    {
        return $this->belongsTo(StateModel::class, 'state_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'city_id');
    }
}

