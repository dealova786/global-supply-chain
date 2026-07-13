<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\WeatherApiController;
use App\Http\Controllers\Api\EconomyApiController;
use App\Http\Controllers\Api\CurrencyApiController;
use App\Http\Controllers\Api\SentimentApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\RiskApiController;
use App\Http\Controllers\Api\PortApiController;
use App\Http\Controllers\Api\ComparisonApiController;
use App\Http\Controllers\Api\WatchlistApiController;

Route::get('/countries', [CountryApiController::class, 'index']);
Route::get('/countries/{id}', [CountryApiController::class, 'show']);

Route::get('/weather', [WeatherApiController::class, 'current']);
Route::get('/economy', [EconomyApiController::class, 'show']);
Route::get('/currency', [CurrencyApiController::class, 'show']);
Route::get('/sentiment', [SentimentApiController::class, 'analyze']);
Route::get('/news', [NewsApiController::class, 'index']);
Route::get('/risk', [RiskApiController::class, 'show']);
Route::get('/risk/history', [RiskApiController::class, 'history']);
Route::get('/ports', [PortApiController::class, 'index']);
Route::get('/compare', [ComparisonApiController::class, 'compare']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/watchlists', [WatchlistApiController::class, 'index']);
    Route::post('/watchlists', [WatchlistApiController::class, 'store']);
    Route::delete('/watchlists/{id}', [WatchlistApiController::class, 'destroy']);
});