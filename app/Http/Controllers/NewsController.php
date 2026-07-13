<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\NewsCache;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request, NewsService $newsService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $newsData = collect();

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $newsService->getNewsByCountry($selectedCountry);

                $newsData = NewsCache::where('country_id', $selectedCountry->id)
                    ->orderBy('published_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        }

        $positiveCount = $newsData->where('sentiment', 'Positive')->count();
        $neutralCount = $newsData->where('sentiment', 'Neutral')->count();
        $negativeCount = $newsData->where('sentiment', 'Negative')->count();

        return view('news.index', compact(
            'countries',
            'selectedCountry',
            'newsData',
            'positiveCount',
            'neutralCount',
            'negativeCount'
        ));
    }
}