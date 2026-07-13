<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\WorldBankService;
use Illuminate\Http\Request;

class EconomyApiController extends Controller
{
    public function show(Request $request, WorldBankService $worldBankService)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::find($request->country_id);

        $economicResult = $worldBankService->getEconomicData($country);

        if (!$economicResult['success']) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve economic data',
                'data' => null,
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Economic data retrieved successfully',
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->cca2,
                'currency' => $country->currency_code,
            ],
            'data' => $economicResult['data'],
        ]);
    }
}