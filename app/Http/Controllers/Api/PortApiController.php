<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Port;
use Illuminate\Http\Request;

class PortApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::query();

        if ($request->filled('country')) {
            $query->where('country_name', 'like', '%' . $request->country . '%');
        }

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('port_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('country_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('country_code', 'like', '%' . $request->keyword . '%');
            });
        }

        $ports = $query->orderBy('country_name')->orderBy('port_name')->get();

        return response()->json([
            'status' => true,
            'message' => 'Ports data retrieved successfully',
            'data' => $ports,
        ]);
    }
}