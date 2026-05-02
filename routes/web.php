<?php

//use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\CountryController;
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
// ── Protected application routes (auth required) ──────────────────────────────
Route::middleware('auth')->group(function () {

    Route::redirect('/', '/channels');

    // Channels (full resource)
    Route::resource('channels', ChannelController::class);

    // Sources — standalone index page + nested CRUD under channels
    Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
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
});