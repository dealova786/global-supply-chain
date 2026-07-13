<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SentimentService;
use Illuminate\Http\Request;

class SentimentApiController extends Controller
{
    public function analyze(Request $request, SentimentService $sentimentService)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $result = $sentimentService->analyze($request->text);

        return response()->json([
            'status' => true,
            'message' => 'Sentiment analysis completed successfully',
            'data' => $result,
        ]);
    }
}