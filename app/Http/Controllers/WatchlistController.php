<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('name', 'asc')->get();

        $watchlists = Watchlist::with('country')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('watchlists.index', compact('countries', 'watchlists'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        Watchlist::firstOrCreate([
            'user_id' => Auth::id(),
            'country_id' => $request->country_id,
        ]);

        return redirect()
            ->route('watchlists.index')
            ->with('success', 'Negara berhasil ditambahkan ke watchlist.');
    }

    public function destroy($id)
    {
        $watchlist = Watchlist::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $watchlist->delete();

        return redirect()
            ->route('watchlists.index')
            ->with('success', 'Negara berhasil dihapus dari watchlist.');
    }
}