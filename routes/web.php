<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CountryDashboardController;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\WeatherMapController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::get('/countries', [CountryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('countries.index');

Route::get('/country-dashboard', [CountryDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('country.dashboard');

Route::get('/risk-scores', [RiskController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('risk.index');

Route::get('/weather-map', [WeatherMapController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('weather.map');

Route::get('/currency-dashboard', [CurrencyController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('currency.index');

Route::get('/news-intelligence', [NewsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('news.index');

Route::get('/ports', [PortController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('ports.index');

Route::get('/compare-countries', [ComparisonController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('comparison.index');

Route::get('/watchlists', [WatchlistController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('watchlists.index');

Route::post('/watchlists', [WatchlistController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('watchlists.store');

Route::delete('/watchlists/{id}', [WatchlistController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('watchlists.destroy');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

});

require __DIR__.'/auth.php';
