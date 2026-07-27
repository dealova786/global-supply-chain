<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NegativeWord;
use App\Models\NewsCache;
use App\Models\PositiveWord;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsService
{
    public function getNewsByCountry(Country $country): array
    {
        $cache = NewsCache::where('country_id', $country->id)
            ->latest('published_at')
            ->limit(10)
            ->get();

        if ($cache->count() > 0) {
            $cache = $this->refreshCachedNewsRisk($cache);

            return [
                'success' => true,
                'status' => true,
                'source' => 'cache',
                'message' => 'News retrieved from cache.',
                'data' => $cache,
            ];
        }

        $gnewsResult = $this->fetchFromGNews($country);

        if ($gnewsResult['status'] && $gnewsResult['data']->count() > 0) {
            return [
                'success' => true,
                'status' => true,
                'source' => 'gnews',
                'message' => $gnewsResult['message'],
                'data' => $gnewsResult['data'],
            ];
        }

        return [
            'success' => false,
            'status' => false,
            'source' => 'none',
            'message' => $gnewsResult['message'],
            'data' => collect(),
        ];
    }

    public function getNews(Country $country): array
    {
        return $this->getNewsByCountry($country);
    }

    public function getCountryNews(Country $country): array
    {
        return $this->getNewsByCountry($country);
    }

    private function fetchFromGNews(Country $country): array
    {
        $apiKey = config('services.gnews.api_key');
        $baseUrl = rtrim(config('services.gnews.base_url', 'https://gnews.io/api/v4'), '/');

        if (!$apiKey) {
            return [
                'status' => false,
                'message' => 'GNews API key belum diisi di file .env.',
                'data' => collect(),
            ];
        }

        try {
            $query = $country->name;

            $response = Http::connectTimeout(15)
                ->timeout(30)
                ->retry(3, 1000)
                ->get($baseUrl . '/search', [
                    'q' => $query,
                    'lang' => 'en',
                    'max' => 10,
                    'apikey' => $apiKey,
                ]);

            if ($response->status() === 403) {
                return [
                    'status' => false,
                    'message' => 'GNews API quota harian sudah habis. Tunggu reset besok jam 00:00 UTC atau gunakan API key baru.',
                    'data' => collect(),
                ];
            }

            if ($response->status() === 401) {
                return [
                    'status' => false,
                    'message' => 'GNews API key tidak valid atau belum terbaca.',
                    'data' => collect(),
                ];
            }

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'message' => 'GNews API status: ' . $response->status(),
                    'data' => collect(),
                ];
            }

            $articles = $response->json('articles') ?? [];

            if (!is_array($articles) || count($articles) === 0) {
                return [
                    'status' => false,
                    'message' => 'GNews API tidak menemukan berita untuk query: ' . $query,
                    'data' => collect(),
                ];
            }

            $savedArticles = $this->saveArticles($country, $articles)
                ->unique('url')
                ->values();

            if ($savedArticles->count() === 0) {
                return [
                    'status' => false,
                    'message' => 'GNews API berhasil merespons, tetapi tidak ada artikel yang valid untuk disimpan.',
                    'data' => collect(),
                ];
            }

            return [
                'status' => true,
                'message' => 'News data retrieved successfully from GNews API.',
                'data' => $savedArticles,
            ];
        } catch (\Throwable $e) {
            return [
                'status' => false,
                'message' => 'GNews API gagal: ' . $e->getMessage(),
                'data' => collect(),
            ];
        }
    }

    private function saveArticles(Country $country, array $articles): Collection
    {
        $savedArticles = collect();

        foreach ($articles as $article) {
            $title = $article['title'] ?? null;
            $url = $article['url'] ?? null;

            if (!$title || !$url) {
                continue;
            }

            $description = $article['description'] ?? '';
            $content = $article['content'] ?? '';
            $sourceName = $article['source']['name'] ?? 'GNews';
            $publishedAt = $this->parsePublishedAt($article['publishedAt'] ?? null);

            $savedArticles->push(
                $this->storeNewsArticle(
                    $country,
                    $title,
                    $description,
                    $content,
                    $sourceName,
                    $url,
                    $publishedAt
                )
            );
        }

        return $savedArticles;
    }

    private function storeNewsArticle(
        Country $country,
        string $title,
        string $description,
        string $content,
        string $sourceName,
        string $url,
        $publishedAt
    ) {
        $textForSentiment = $title . ' ' . $description . ' ' . $content;
        $sentiment = $this->analyzeSentiment($textForSentiment);

        return NewsCache::updateOrCreate(
            [
                'country_id' => $country->id,
                'url' => $url,
            ],
            [
                'title' => Str::limit($title, 250, ''),
                'description' => Str::limit($description, 250, ''),
                'content' => Str::limit($content, 1000, ''),
                'source' => Str::limit($sourceName, 100, ''),
                'published_at' => $publishedAt,
                'sentiment' => $sentiment['sentiment'],
                'positive_score' => $sentiment['positive_score'],
                'negative_score' => $sentiment['negative_score'],
                'news_risk' => $sentiment['news_risk'],
            ]
        );
    }

    private function refreshCachedNewsRisk(Collection $cache): Collection
    {
        return $cache->map(function ($news) {
            if ((int) $news->news_risk > 0) {
                return $news;
            }

            $sentiment = $news->sentiment ?? 'Neutral';

            $newsRisk = $this->calculateNewsRiskFromSentiment(
                $sentiment,
                (int) ($news->positive_score ?? 0),
                (int) ($news->negative_score ?? 0)
            );

            $news->news_risk = $newsRisk;
            $news->save();

            return $news;
        });
    }

    private function analyzeSentiment(string $text): array
    {
        $positiveWords = PositiveWord::pluck('word')
            ->map(function ($word) {
                return strtolower(trim($word));
            })
            ->filter()
            ->values()
            ->toArray();

        $negativeWords = NegativeWord::pluck('word')
            ->map(function ($word) {
                return strtolower(trim($word));
            })
            ->filter()
            ->values()
            ->toArray();

        $cleanText = strtolower($text);
        $cleanText = preg_replace('/[^a-zA-Z\s]/', ' ', $cleanText);
        $tokens = preg_split('/\s+/', $cleanText);

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($tokens as $token) {
            $token = trim($token);

            if (!$token) {
                continue;
            }

            $normalizedToken = $this->normalizeWord($token);

            if (in_array($token, $positiveWords) || in_array($normalizedToken, $positiveWords)) {
                $positiveScore++;
            }

            if (in_array($token, $negativeWords) || in_array($normalizedToken, $negativeWords)) {
                $negativeScore++;
            }
        }

        if ($positiveScore > $negativeScore) {
            $sentiment = 'Positive';
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = 'Negative';
        } else {
            $sentiment = 'Neutral';
        }

        return [
            'sentiment' => $sentiment,
            'positive_score' => $positiveScore,
            'negative_score' => $negativeScore,
            'news_risk' => $this->calculateNewsRiskFromSentiment($sentiment, $positiveScore, $negativeScore),
        ];
    }

    private function calculateNewsRiskFromSentiment(
        ?string $sentiment,
        int $positiveScore = 0,
        int $negativeScore = 0
    ): int {
        $sentiment = strtolower($sentiment ?? 'neutral');

        if ($sentiment === 'positive') {
            return 20;
        }

        if ($sentiment === 'negative') {
            if ($negativeScore >= 3) {
                return 80;
            }

            return 60;
        }

        return 50;
    }

    private function normalizeWord(string $word): string
    {
        foreach (['ing', 'ed', 'es', 's'] as $suffix) {
            if (Str::endsWith($word, $suffix) && strlen($word) > strlen($suffix) + 2) {
                return Str::beforeLast($word, $suffix);
            }
        }

        return $word;
    }

    private function parsePublishedAt($publishedAt)
    {
        try {
            return $publishedAt ? Carbon::parse($publishedAt) : now();
        } catch (\Throwable $e) {
            return now();
        }
    }
}