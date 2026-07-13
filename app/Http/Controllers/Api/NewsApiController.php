<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request, NewsService $newsService)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);

        $newsResult = $newsService->getNewsByCountry($country);

        if (!$newsResult['success']) {
            return response()->json([
                'status' => false,
                'message' => $newsResult['message'],
                'data' => [],
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'News data retrieved successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
            ],
            'data' => $newsResult['data'],
        ]);
    }
}