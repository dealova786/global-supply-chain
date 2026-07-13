<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistApiController extends Controller
{
    public function index()
    {
        $watchlists = Watchlist::with('country')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Watchlist data retrieved successfully',
            'data' => $watchlists,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $watchlist = Watchlist::firstOrCreate([
            'user_id' => Auth::id(),
            'country_id' => $request->country_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Country added to watchlist successfully',
            'data' => $watchlist,
        ]);
    }

    public function destroy($id)
    {
        $watchlist = Watchlist::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$watchlist) {
            return response()->json([
                'status' => false,
                'message' => 'Watchlist data not found',
            ], 404);
        }

        $watchlist->delete();

        return response()->json([
            'status' => true,
            'message' => 'Country removed from watchlist successfully',
        ]);
    }
}