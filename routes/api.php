<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryApiController;

Route::get('/countries', [CountryApiController::class, 'index']);
Route::get('/countries/{id}', [CountryApiController::class, 'show']);