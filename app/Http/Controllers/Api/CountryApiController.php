<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;

class CountryApiController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('name', 'asc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Countries data retrieved successfully',
            'data' => $countries,
        ]);
    }

    public function show($id)
    {
        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'status' => false,
                'message' => 'Country not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Country detail retrieved successfully',
            'data' => $country,
        ]);
    }
}