<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request, NewsService $newsService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $newsData = collect();
        $message = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $result = $newsService->getNewsByCountry($selectedCountry);

                $message = $result['message'] ?? null;

                $newsData = collect($result['data'] ?? []);
            }
        }

        $totalNews = $newsData->count();

        $positiveNews = $newsData->filter(function ($news) {
            return ($news['sentiment'] ?? $news->sentiment ?? null) === 'Positive';
        })->count();

        $neutralNews = $newsData->filter(function ($news) {
            return ($news['sentiment'] ?? $news->sentiment ?? null) === 'Neutral';
        })->count();

        $negativeNews = $newsData->filter(function ($news) {
            return ($news['sentiment'] ?? $news->sentiment ?? null) === 'Negative';
        })->count();

        return view('news.index', compact(
            'countries',
            'selectedCountry',
            'newsData',
            'totalNews',
            'positiveNews',
            'neutralNews',
            'negativeNews',
            'message'
        ));
    }
}