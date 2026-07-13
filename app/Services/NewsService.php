<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsCache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsService
{
    public function __construct(
        protected SentimentService $sentimentService
    ) {}

    public function getNewsByCountry(Country $country): array
    {
        $apiKey = config('services.gnews.api_key');
        $baseUrl = config('services.gnews.base_url');

        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'GNews API key is not configured',
                'data' => [],
            ];
        }

        $queries = [
            $country->name . ' economy',
            $country->name . ' trade',
            $country->name . ' logistics',
            $country->name . ' shipping',
            $country->name . ' supply chain',
        ];

        $allArticles = [];

        foreach ($queries as $query) {
            $response = Http::timeout(15)->get($baseUrl . '/search', [
                'q' => $query,
                'lang' => 'en',
                'max' => 5,
                'apikey' => $apiKey,
            ]);

            if (!$response->successful()) {
                continue;
            }

            $json = $response->json();
            $articles = $json['articles'] ?? [];

            foreach ($articles as $article) {
                $url = $article['url'] ?? null;

                if (!$url) {
                    continue;
                }

                $allArticles[$url] = $article;
            }

            if (count($allArticles) >= 10) {
                break;
            }
        }

        $results = [];

        foreach (array_slice($allArticles, 0, 10) as $article) {
            $title = $article['title'] ?? 'Untitled News';
            $description = $article['description'] ?? null;
            $content = $article['content'] ?? null;
            $url = $article['url'] ?? null;
            $source = $article['source']['name'] ?? null;
            $publishedAt = $article['publishedAt'] ?? null;

            $textForAnalysis = trim($title . ' ' . $description . ' ' . $content);

            $sentimentResult = $this->sentimentService->analyze($textForAnalysis);

            $news = NewsCache::updateOrCreate(
                [
                    'url' => $url,
                ],
                [
                    'country_id' => $country->id,
                    'title' => Str::limit($title, 250, ''),
                    'description' => $description,
                    'content' => $content,
                    'source' => $source,
                    'published_at' => $publishedAt ? Carbon::parse($publishedAt) : null,
                    'sentiment' => $sentimentResult['sentiment'],
                    'positive_score' => $sentimentResult['positive_score'],
                    'negative_score' => $sentimentResult['negative_score'],
                    'neutral_score' => $sentimentResult['neutral_score'],
                ]
            );

            $results[] = [
                'id' => $news->id,
                'title' => $news->title,
                'description' => $news->description,
                'url' => $news->url,
                'source' => $news->source,
                'published_at' => $news->published_at,
                'sentiment' => $news->sentiment,
                'positive_score' => $news->positive_score,
                'negative_score' => $news->negative_score,
                'neutral_score' => $news->neutral_score,
                'news_risk' => $sentimentResult['news_risk'],
            ];
        }

        return [
            'success' => true,
            'message' => 'News data retrieved successfully',
            'data' => $results,
        ];
    }
}