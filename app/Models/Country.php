<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'official_name',
        'cca2',
        'cca3',
        'capital',
        'region',
        'subregion',
        'latitude',
        'longitude',
        'currency_code',
        'currency_name',
        'language',
        'population',
        'flag_url',
    ];

    public function economicIndicators()
    {
        return $this->hasMany(EconomicIndicator::class);
    }

    public function weatherCaches()
    {
        return $this->hasMany(WeatherCache::class);
    }

    public function currencyRates()
    {
        return $this->hasMany(CurrencyRate::class);
    }

    public function newsCaches()
    {
        return $this->hasMany(NewsCache::class);
    }

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }
}

