<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'country_id',
        'title',
        'description',
        'content',
        'source',
        'url',
        'published_at',
        'sentiment',
        'positive_score',
        'negative_score',
        'news_risk',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'positive_score' => 'integer',
        'negative_score' => 'integer',
        'news_risk' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}