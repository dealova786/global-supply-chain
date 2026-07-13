<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class RiskController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $riskScores = collect();

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $riskScores = RiskScore::where('country_id', $selectedCountry->id)
                    ->orderBy('calculated_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        }

        $chartLabels = $riskScores
            ->sortBy('calculated_at')
            ->map(function ($risk) {
                return $risk->calculated_at
                    ? date('d M H:i', strtotime($risk->calculated_at))
                    : $risk->created_at->format('d M H:i');
            })
            ->values();

        $chartData = $riskScores
            ->sortBy('calculated_at')
            ->pluck('total_score')
            ->values();

        return view('risk.index', compact(
            'countries',
            'selectedCountry',
            'riskScores',
            'chartLabels',
            'chartData'
        ));
    }
}