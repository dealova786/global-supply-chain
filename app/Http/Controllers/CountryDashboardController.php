<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class CountryDashboardController extends Controller
{
    public function index(Request $request, WeatherService $weatherService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $weatherData = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $weatherResult = $weatherService->getCurrentWeather($selectedCountry);

                if ($weatherResult['success']) {
                    $weatherData = $weatherResult['data'];
                }
            }
        }

        return view('country-dashboard.index', compact(
            'countries',
            'selectedCountry',
            'weatherData'
        ));
    }
}