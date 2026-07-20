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

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);
        }

        if ($selectedCountry) {
            $riskRows = RiskScore::with('country')
                ->where('country_id', $selectedCountry->id)
                ->latest('created_at')
                ->limit(20)
                ->get();

            $chartRows = RiskScore::where('country_id', $selectedCountry->id)
                ->latest('created_at')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        } else {
            $riskRows = RiskScore::with('country')
                ->latest('created_at')
                ->get()
                ->unique('country_id')
                ->take(20)
                ->values();

            $chartRows = RiskScore::latest('created_at')
                ->limit(12)
                ->get()
                ->reverse()
                ->values();
        }

        $latestRisk = $riskRows->first();

        $lowRiskCount = $riskRows->where('risk_level', 'Low')->count();
        $mediumRiskCount = $riskRows->where('risk_level', 'Medium')->count();
        $highRiskCount = $riskRows->where('risk_level', 'High')->count();

        $averageRiskScore = $riskRows->count() > 0
            ? round($riskRows->avg('total_score'))
            : 0;

        $chartLabels = $chartRows->map(function ($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d M');
        });

        $chartValues = $chartRows->map(function ($item) {
            return round($item->total_score);
        });

        return view('risk.index', compact(
            'countries',
            'selectedCountry',
            'riskRows',
            'latestRisk',
            'lowRiskCount',
            'mediumRiskCount',
            'highRiskCount',
            'averageRiskScore',
            'chartLabels',
            'chartValues'
        ));
    }
}