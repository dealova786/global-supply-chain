<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Port;
use App\Services\PortSyncService;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $query = Port::with('country')
            ->orderBy('country_name', 'asc')
            ->orderBy('port_name', 'asc');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $ports = $query->get();

        $selectedCountry = null;

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);
        }

        return view('admin.ports.index', compact(
            'ports',
            'countries',
            'selectedCountry'
        ));
    }

    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->get();

        return view('admin.ports.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'port_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'region' => 'nullable|string|max:100',
            'harbor_size' => 'nullable|string|max:100',
            'port_type' => 'nullable|string|max:100',
        ]);

        $country = $request->country_id
            ? Country::find($request->country_id)
            : null;

        Port::create([
            'country_id' => $country?->id,
            'port_name' => $request->port_name,
            'country_name' => $country?->name,
            'country_code' => $country?->cca2,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'region' => $request->region ?? $country?->region,
            'harbor_size' => $request->harbor_size,
            'port_type' => $request->port_type,
        ]);

        return redirect()
            ->route('admin.ports.index', ['country_id' => $country?->id])
            ->with('success', 'Data port berhasil ditambahkan.');
    }

    public function edit(Port $port)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        return view('admin.ports.edit', compact('port', 'countries'));
    }

    public function update(Request $request, Port $port)
    {
        $request->validate([
            'country_id' => 'nullable|exists:countries,id',
            'port_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'region' => 'nullable|string|max:100',
            'harbor_size' => 'nullable|string|max:100',
            'port_type' => 'nullable|string|max:100',
        ]);

        $country = $request->country_id
            ? Country::find($request->country_id)
            : null;

        $port->update([
            'country_id' => $country?->id,
            'port_name' => $request->port_name,
            'country_name' => $country?->name,
            'country_code' => $country?->cca2,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'region' => $request->region ?? $country?->region,
            'harbor_size' => $request->harbor_size,
            'port_type' => $request->port_type,
        ]);

        return redirect()
            ->route('admin.ports.index', ['country_id' => $country?->id])
            ->with('success', 'Data port berhasil diperbarui.');
    }

    public function destroy(Port $port)
    {
        $countryId = $port->country_id;

        $port->delete();

        return redirect()
            ->route('admin.ports.index', ['country_id' => $countryId])
            ->with('success', 'Data port berhasil dihapus.');
    }

    public function syncApi(Request $request, PortSyncService $portSyncService)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $country = Country::findOrFail($request->country_id);

        Port::where('country_id', $country->id)->delete();

        $result = $portSyncService->syncByCountry($country);

        if ($result['status']) {
            return redirect()
                ->route('admin.ports.index', ['country_id' => $country->id])
                ->with(
                    'success',
                    $result['message'] .
                    ' Synced: ' . $result['synced'] .
                    ', skipped: ' . $result['skipped']
                );
        }

        return redirect()
            ->route('admin.ports.index', ['country_id' => $country->id])
            ->with('error', $result['message']);
    }
}