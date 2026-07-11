<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'country_id',
        'port_name',
        'country_name',
        'country_code',
        'latitude',
        'longitude',
        'region',
        'harbor_size',
        'port_type',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}