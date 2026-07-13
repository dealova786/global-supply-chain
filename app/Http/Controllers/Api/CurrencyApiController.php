<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyApiController extends Controller
{
    public function show(Request $request, CurrencyService $currencyService)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'target_currency' => 'nullable|string|max:10',
        ]);

        $country = Country::find($request->country_id);
        $targetCurrency = $request->target_currency ?? 'USD';

        $currencyResult = $currencyService->getCurrencyRate($country, strtoupper($targetCurrency));

        if (!$currencyResult['success']) {
            return response()->json([
                'status' => false,
                'message' => $currencyResult['message'],
                'data' => null,
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Currency data retrieved successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'currency_code' => $country->currency_code,
            ],
            'data' => $currencyResult['data'],
        ]);
    }
}