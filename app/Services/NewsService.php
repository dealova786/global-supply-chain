<?php

namespace App\Services;

use App\Models\Country;
use App\Models\NewsCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsService
{
    protected SentimentService $sentimentService;

    public function __construct(SentimentService $sentimentService)
    {
        $this->sentimentService = $sentimentService;
    }

    public function getNewsByCountry(Country $country): array
    {
        $apiKey = config('services.gnews.api_key');
        $baseUrl = config('services.gnews.base_url', 'https://gnews.io/api/v4');

        $recentCache = NewsCache::where('country_id', $country->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        if ($recentCache->isNotEmpty()) {
            return [
                'success' => true,
                'source' => 'cache',
                'message' => 'Menampilkan news cache terbaru.',
                'data' => $recentCache,
            ];
        }

        if (!$apiKey) {
            return $this->getCachedNews(
                $country,
                'GNews API key belum tersedia. Menampilkan cache jika ada.'
            );
        }

        try {
            $savedNews = collect();
            $usedUrls = [];

            if ($country->cca2) {
                $this->fetchAndSaveArticles(
                    country: $country,
                    endpoint: $baseUrl . '/top-headlines',
                    params: [
                        'category' => 'business',
                        'country' => strtolower($country->cca2),
                        'lang' => 'en',
                        'max' => 5,
                        'apikey' => $apiKey,
                    ],
                    savedNews: $savedNews,
                    usedUrls: $usedUrls
                );
            }

            if ($savedNews->count() < 5) {
                $queries = [
                    '"' . $country->name . '" economy OR trade OR business',
                    '"' . $country->name . '" logistics OR import OR export',
                    '"' . $country->name . '" supply chain',
                ];

                if ($country->official_name && $country->official_name !== $country->name) {
                    $queries[] = '"' . $country->official_name . '" economy OR trade';
                }

                if ($country->capital) {
                    $queries[] = '"' . $country->capital . '" "' . $country->name . '" economy';
                }

                foreach ($queries as $query) {
                    $this->fetchAndSaveArticles(
                        country: $country,
                        endpoint: $baseUrl . '/search',
                        params: [
                            'q' => Str::limit($query, 190, ''),
                            'lang' => 'en',
                            'max' => 5,
                            'sortby' => 'publishedAt',
                            'nullable' => 'description,content',
                            'apikey' => $apiKey,
                        ],
                        savedNews: $savedNews,
                        usedUrls: $usedUrls
                    );

                    if ($savedNews->count() >= 10) {
                        break;
                    }
                }
            }

            if ($savedNews->isEmpty() && $country->region) {
                $this->fetchAndSaveArticles(
                    country: $country,
                    endpoint: $baseUrl . '/search',
                    params: [
                        'q' => '"' . $country->region . '" global trade OR supply chain OR logistics',
                        'lang' => 'en',
                        'max' => 5,
                        'sortby' => 'publishedAt',
                        'nullable' => 'description,content',
                        'apikey' => $apiKey,
                    ],
                    savedNews: $savedNews,
                    usedUrls: $usedUrls
                );
            }

            if ($savedNews->isEmpty()) {
                return $this->getCachedNews(
                    $country,
                    'GNews API tidak mengembalikan artikel untuk negara ini. Menampilkan cache jika ada.'
                );
            }

            return [
                'success' => true,
                'source' => 'api',
                'message' => 'News data retrieved successfully.',
                'data' => $savedNews,
            ];

        } catch (\Throwable $e) {
            return $this->getCachedNews(
                $country,
                'GNews API timeout atau gagal. Menampilkan cache terakhir jika tersedia.'
            );
        }
    }

    private function fetchAndSaveArticles(
        Country $country,
        string $endpoint,
        array $params,
        $savedNews,
        array &$usedUrls
    ): void {
        try {
            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->retry(1, 500)
                ->get($endpoint, $params);

            if (!$response->successful()) {
                return;
            }

            $articles = $response->json('articles') ?? [];

            foreach ($articles as $article) {
                if ($savedNews->count() >= 10) {
                    return;
                }

                $url = $article['url'] ?? null;

                if (!$url || in_array($url, $usedUrls)) {
                    continue;
                }

                $usedUrls[] = $url;

                $title = $article['title'] ?? '-';
                $description = $article['description'] ?? '';
                $content = $article['content'] ?? '';
                $source = $article['source']['name'] ?? '-';
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
                        'description' => Str::limit($description, 250, ''),
                        'content' => Str::limit($content, 1000, ''),
                        'source' => Str::limit($source, 100, ''),
                        'published_at' => $publishedAt ? Carbon::parse($publishedAt) : now(),
                        'sentiment' => $sentimentResult['sentiment'],
                        'positive_score' => $sentimentResult['positive_score'],
                        'negative_score' => $sentimentResult['negative_score'],
                        'neutral_score' => $sentimentResult['neutral_score'],
                        'news_risk' => $sentimentResult['news_risk'],
                    ]
                );

                $savedNews->push($news);
            }

        } catch (\Throwable $e) {
            return;
        }
    }

    private function getCachedNews(Country $country, string $message): array
    {
        $cache = NewsCache::where('country_id', $country->id)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        if ($cache->isEmpty()) {
            return [
                'success' => false,
                'source' => 'none',
                'message' => $message . ' Belum ada cache berita untuk negara ini.',
                'data' => collect(),
            ];
        }

        return [
            'success' => true,
            'source' => 'cache',
            'message' => $message,
            'data' => $cache,
        ];
    }
}