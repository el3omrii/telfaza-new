<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Authentication (guest only) ───────────────────────────────────────────────
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// ── Public sitemap endpoints ────────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap.xml');
Route::get('/video-sitemap.xml', [SitemapController::class, 'videoSitemap'])->name('video-sitemap.xml');
Route::get('/image-sitemap.xml', [SitemapController::class, 'imageSitemap'])->name('image-sitemap.xml');

// ── Protected application routes (auth required) ──────────────────────────────
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/dashboard');

    // Dashboard (default landing page after login)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Channels (full resource)
    Route::resource('channels', ChannelController::class);

    // Sources — standalone index page + nested CRUD under channels
    Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
    Route::post('/sources/{source}/toggle', [SourceController::class, 'toggle'])->name('sources.toggle');
    Route::resource('channels.sources', SourceController::class)
         ->only(['create', 'store', 'edit', 'update', 'destroy'])
         ->shallow();

    // Categories (full resource)
    Route::resource('categories', CategoryController::class);

    // Tags — managed inline; extra AJAX endpoint for quick-create from channel form
    Route::resource('tags', TagController::class)
         ->only(['index', 'store', 'update', 'destroy']);
    Route::post('/tags/quick-create', [TagController::class, 'quickCreate'])
         ->name('tags.quick-create');

    // Countries (no show/create/edit pages — managed inline)
    Route::resource('countries', CountryController::class)
         ->only(['index', 'store', 'update', 'destroy']);

    // AI
    Route::post('/ai/generate-description', [GeminiController::class, 'generateDescription'])
         ->name('ai.generate-description');

    // Reports 
    Route::resource('reports', ReportController::class)
         ->only(['index', 'edit', 'update', 'destroy']);

    // Sitemaps
    Route::get('/sitemaps', [SitemapController::class, 'index'])->name('sitemaps.index');
    Route::post('/sitemaps/generate', [SitemapController::class, 'generate'])->name('sitemaps.generate');

    // Profile
    Route::get('/profile',               [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',               [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',      [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',            [ProfileController::class, 'destroy'])->name('profile.destroy');
});