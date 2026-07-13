<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $query = Port::query();

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('port_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('country_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('country_code', 'like', '%' . $request->keyword . '%');
            });
        }

        $ports = $query->orderBy('country_name')->orderBy('port_name')->get();

        return view('ports.index', compact('ports', 'countries'));
    }
}