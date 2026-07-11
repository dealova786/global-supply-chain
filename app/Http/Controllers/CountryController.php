<?php

namespace App\Http\Controllers;

use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('name', 'asc')->get();

        return view('countries.index', compact('countries'));
    }
}