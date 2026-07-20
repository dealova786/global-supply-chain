<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\WeatherCache;
use App\Services\PortSyncService;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request, PortSyncService $portSyncService)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $selectedCountry = null;
        $syncMessage = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);

            if ($selectedCountry) {
                $existingPorts = Port::where('country_id', $selectedCountry->id)->count();

                if ($existingPorts === 0) {
                    $result = $portSyncService->syncByCountry($selectedCountry);

                    $syncMessage = $result['message'] .
                        ' Synced: ' . $result['synced'] .
                        ', skipped: ' . $result['skipped'];
                }
            }
        }

        $query = Port::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('port_name', 'like', '%' . $keyword . '%')
                    ->orWhere('country_name', 'like', '%' . $keyword . '%')
                    ->orWhere('country_code', 'like', '%' . $keyword . '%')
                    ->orWhere('region', 'like', '%' . $keyword . '%');
            });
        }

        $ports = $query->orderBy('country_name', 'asc')
            ->orderBy('port_name', 'asc')
            ->limit(300)
            ->get();

        $ports = $ports->map(function ($port) {
            $tracking = $this->buildPortTracking($port);

            $port->tracking_status = $tracking['tracking_status'];
            $port->tracking_badge = $tracking['tracking_badge'];
            $port->risk_score = $tracking['risk_score'];
            $port->risk_level = $tracking['risk_level'];
            $port->last_updated = $tracking['last_updated'];

            return $port;
        });

        $allPorts = Port::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('country_name', 'asc')
            ->orderBy('port_name', 'asc')
            ->get();

        $distanceData = null;

        if ($request->filled('origin_port_id') && $request->filled('destination_port_id')) {
            $originPort = Port::find($request->origin_port_id);
            $destinationPort = Port::find($request->destination_port_id);

            if ($originPort && $destinationPort) {
                $distanceKm = $this->calculateDistanceKm(
                    $originPort->latitude,
                    $originPort->longitude,
                    $destinationPort->latitude,
                    $destinationPort->longitude
                );

                $averageShipSpeedKmH = 25;
                $estimatedHours = $distanceKm / $averageShipSpeedKmH;
                $estimatedDays = $estimatedHours / 24;

                $distanceData = [
                    'origin' => [
                        'id' => $originPort->id,
                        'port_name' => $originPort->port_name,
                        'country_name' => $originPort->country_name,
                        'latitude' => (float) $originPort->latitude,
                        'longitude' => (float) $originPort->longitude,
                    ],
                    'destination' => [
                        'id' => $destinationPort->id,
                        'port_name' => $destinationPort->port_name,
                        'country_name' => $destinationPort->country_name,
                        'latitude' => (float) $destinationPort->latitude,
                        'longitude' => (float) $destinationPort->longitude,
                    ],
                    'distance_km' => round($distanceKm, 2),
                    'estimated_hours' => round($estimatedHours, 2),
                    'estimated_days' => round($estimatedDays, 2),
                    'average_speed' => $averageShipSpeedKmH,
                ];
            }
        }

        $portMarkers = $ports->map(function ($port) {
            return [
                'port_name' => $port->port_name,
                'country_name' => $port->country_name,
                'country_code' => $port->country_code,
                'region' => $port->region,
                'harbor_size' => $port->harbor_size,
                'port_type' => $port->port_type,
                'latitude' => (float) $port->latitude,
                'longitude' => (float) $port->longitude,
                'tracking_status' => $port->tracking_status,
                'risk_score' => $port->risk_score,
                'risk_level' => $port->risk_level,
            ];
        })->values();

        return view('ports.index', compact(
            'countries',
            'ports',
            'allPorts',
            'portMarkers',
            'selectedCountry',
            'syncMessage',
            'distanceData'
        ));
    }

    private function buildPortTracking(Port $port): array
    {
        $latestRisk = RiskScore::where('country_id', $port->country_id)
            ->latest('created_at')
            ->first();

        $latestWeather = WeatherCache::where('country_id', $port->country_id)
            ->latest('recorded_at')
            ->first();

        $riskScore = $latestRisk?->total_score;

        if (is_null($riskScore)) {
            $riskScore = $latestWeather?->weather_risk;
        }

        if (is_null($riskScore)) {
            return [
                'tracking_status' => 'Not Tracked',
                'tracking_badge' => 'secondary',
                'risk_score' => '-',
                'risk_level' => 'No Data',
                'last_updated' => null,
            ];
        }

        if ($riskScore >= 70) {
            $status = 'High Risk';
            $badge = 'danger';
            $level = 'High';
        } elseif ($riskScore >= 40) {
            $status = 'Moderate';
            $badge = 'warning';
            $level = 'Medium';
        } else {
            $status = 'Normal';
            $badge = 'success';
            $level = 'Low';
        }

        return [
            'tracking_status' => $status,
            'tracking_badge' => $badge,
            'risk_score' => round($riskScore),
            'risk_level' => $level,
            'last_updated' => $latestRisk?->created_at
                ?? $latestWeather?->recorded_at
                ?? $port->updated_at,
        ];
    }

    private function calculateDistanceKm($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;

        $lat1 = deg2rad((float) $lat1);
        $lon1 = deg2rad((float) $lon1);
        $lat2 = deg2rad((float) $lat2);
        $lon2 = deg2rad((float) $lon2);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}