<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Authentication (guest only) ───────────────────────────────────────────────
Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

// ── Protected application routes (auth required) ──────────────────────────────
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/channels');

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
    // Profile
    Route::get('/profile',               [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',               [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',      [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',            [ProfileController::class, 'destroy'])->name('profile.destroy');
});