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
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\SyncController as AdminSyncController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\SentimentWordController as AdminSentimentWordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
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

    Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [AdminArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');

    Route::get('/sync-center', [AdminSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/countries', [AdminSyncController::class, 'syncCountries'])->name('sync.countries');
    Route::post('/ports/sync-api', [AdminPortController::class, 'syncApi'])
    ->name('ports.syncApi');

    Route::resource('ports', AdminPortController::class)->except(['show']);
    Route::get('/sentiment-words', [AdminSentimentWordController::class, 'index'])
        ->name('sentiment-words.index');

    Route::post('/sentiment-words', [AdminSentimentWordController::class, 'store'])
        ->name('sentiment-words.store');

    Route::delete('/sentiment-words/{type}/{id}', [AdminSentimentWordController::class, 'destroy'])
        ->name('sentiment-words.destroy');
});

});

require __DIR__.'/auth.php';
