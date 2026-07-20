<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CurrencyRate;
use App\Models\EconomicIndicator;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Models\WeatherCache;
use Illuminate\Http\Request;

class ComparisonController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->get();

                $countryA = $request->filled('country_a')
            ? Country::find($request->country_a)
            : null;

        $countryB = $request->filled('country_b')
            ? Country::find($request->country_b)
            : null;

        $dataA = ($countryA && $countryB)
            ? $this->buildComparisonData($countryA)
            : null;

        $dataB = ($countryA && $countryB)
            ? $this->buildComparisonData($countryB)
            : null;

        $chartLabels = [
            $countryA?->name ?? 'Country A',
            $countryB?->name ?? 'Country B',
        ];

        $gdpChartData = [
            $dataA['economy']->gdp ?? 0,
            $dataB['economy']->gdp ?? 0,
        ];

        $inflationChartData = [
            $dataA['economy']->inflation ?? 0,
            $dataB['economy']->inflation ?? 0,
        ];

        $riskChartData = [
            $dataA['risk']->total_score ?? 0,
            $dataB['risk']->total_score ?? 0,
        ];

        $weatherRiskChartData = [
            $dataA['risk']->weather_risk ?? 0,
            $dataB['risk']->weather_risk ?? 0,
        ];

        $currencyRiskChartData = [
            $dataA['risk']->currency_risk ?? 0,
            $dataB['risk']->currency_risk ?? 0,
        ];

        $newsRiskChartData = [
            $dataA['risk']->news_risk ?? 0,
            $dataB['risk']->news_risk ?? 0,
        ];

        $decision = ($countryA && $countryB && $dataA && $dataB)
            ? $this->buildDecisionSummary($countryA, $countryB, $dataA, $dataB)
            : null;

        $decision = $this->buildDecisionSummary($countryA, $countryB, $dataA, $dataB);

        return view('comparison.index', compact(
            'countries',
            'countryA',
            'countryB',
            'dataA',
            'dataB',
            'chartLabels',
            'gdpChartData',
            'inflationChartData',
            'riskChartData',
            'weatherRiskChartData',
            'currencyRiskChartData',
            'newsRiskChartData',
            'decision'
        ));
    }

    private function buildComparisonData(Country $country): array
    {
        $latestWeather = WeatherCache::where('country_id', $country->id)
            ->latest('recorded_at')
            ->latest('id')
            ->first();

        $latestEconomy = EconomicIndicator::where('country_id', $country->id)
            ->latest('year')
            ->latest('id')
            ->first();

        $latestCurrency = CurrencyRate::where('country_id', $country->id)
            ->latest('recorded_at')
            ->latest('id')
            ->first();

        $latestNews = NewsCache::where('country_id', $country->id)
            ->latest('published_at')
            ->limit(10)
            ->get();

        $latestRisk = RiskScore::where('country_id', $country->id)
            ->latest('created_at')
            ->latest('id')
            ->first();

        $weatherRisk = $latestRisk?->weather_risk
            ?? $latestWeather?->weather_risk
            ?? 20;

        $inflationRisk = $latestRisk?->inflation_risk
            ?? $this->calculateInflationRisk($latestEconomy?->inflation);

        $currencyRisk = $latestRisk?->currency_risk
            ?? $latestCurrency?->currency_risk
            ?? 20;

        $newsRisk = $latestRisk?->news_risk
            ?? ($latestNews->count() > 0 ? round($latestNews->avg('news_risk')) : 50);

        $totalScore = $latestRisk?->total_score
            ?? round(($weatherRisk * 0.3) + ($inflationRisk * 0.2) + ($newsRisk * 0.4) + ($currencyRisk * 0.1));

        $riskLevel = $latestRisk?->risk_level
            ?? $this->determineRiskLevel($totalScore);

        return [
            'weather' => (object) [
                'temperature' => $latestWeather?->temperature ?? 0,
                'rainfall' => $latestWeather?->rainfall ?? 0,
                'wind_speed' => $latestWeather?->wind_speed ?? 0,
                'weather_condition' => $latestWeather?->weather_condition ?? 'No Data',
                'weather_risk' => $weatherRisk,
            ],

            'economy' => (object) [
                'gdp' => $latestEconomy?->gdp ?? 0,
                'inflation' => $latestEconomy?->inflation,
                'population' => $latestEconomy?->population ?? $country->population ?? 0,
                'exports' => $latestEconomy?->exports ?? 0,
                'imports' => $latestEconomy?->imports ?? 0,
                'year' => $latestEconomy?->year ?? '-',
            ],

            'currency' => (object) [
                'base_currency' => $latestCurrency?->base_currency ?? $country->currency_code ?? '-',
                'target_currency' => $latestCurrency?->target_currency ?? 'USD',
                'exchange_rate' => $latestCurrency?->exchange_rate ?? 0,
                'currency_risk' => $currencyRisk,
            ],

            'news' => $latestNews,

            'risk' => (object) [
                'total_score' => $totalScore,
                'risk_level' => $riskLevel,
                'weather_risk' => $weatherRisk,
                'inflation_risk' => $inflationRisk,
                'news_risk' => $newsRisk,
                'currency_risk' => $currencyRisk,
            ],
        ];
    }

    private function calculateInflationRisk($inflation): int
    {
        if (is_null($inflation)) {
            return 50;
        }

        if ($inflation <= 3) {
            return 20;
        }

        if ($inflation <= 7) {
            return 50;
        }

        return 80;
    }

    private function determineRiskLevel($score): string
    {
        if ($score <= 30) {
            return 'Low';
        }

        if ($score <= 60) {
            return 'Medium';
        }

        return 'High';
    }

    private function buildDecisionSummary($countryA, $countryB, $dataA, $dataB): ?array
    {
        if (!$countryA || !$countryB || !$dataA || !$dataB) {
            return null;
        }

        $scoreA = $dataA['risk']->total_score ?? 0;
        $scoreB = $dataB['risk']->total_score ?? 0;

        if ($scoreA < $scoreB) {
            $winner = $countryA->name;
            $loser = $countryB->name;
            $gap = $scoreB - $scoreA;
        } elseif ($scoreB < $scoreA) {
            $winner = $countryB->name;
            $loser = $countryA->name;
            $gap = $scoreA - $scoreB;
        } else {
            return [
                'winner' => null,
                'title' => 'Kedua negara relatif seimbang',
                'summary' => 'Berdasarkan hasil perbandingan, kedua negara memiliki total risk score yang sama. Perusahaan perlu melihat indikator tambahan seperti inflasi, kurs, cuaca, dan berita sebelum menentukan keputusan.',
            ];
        }

        return [
            'winner' => $winner,
            'title' => $winner . ' lebih unggul untuk dipertimbangkan',
            'summary' => $winner . ' memiliki total risk score lebih rendah dibandingkan ' . $loser . ' dengan selisih ' . $gap . ' poin. Hal ini menunjukkan bahwa ' . $winner . ' relatif lebih aman untuk dipertimbangkan dalam keputusan rantai pasok karena tingkat risikonya lebih rendah.',
        ];
    }
}