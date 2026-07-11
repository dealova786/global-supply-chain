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
        'url',
        'source',
        'published_at',
        'sentiment',
        'positive_score',
        'negative_score',
        'neutral_score',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}