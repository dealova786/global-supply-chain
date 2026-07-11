<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'country_id',
        'base_currency',
        'target_currency',
        'exchange_rate',
        'currency_risk',
        'rate_date',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}