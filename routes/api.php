<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Public article routes
Route::prefix('articles')->name('articles.')->group(function () {
    Route::get('/', [ArticleController::class, 'index'])->name('index');
    Route::get('/{id}', [ArticleController::class, 'show'])->name('show')->where('id', '[0-9]+');
});

// Additional article-related routes
Route::name('articles.')->group(function () {
    Route::get('/sources', [ArticleController::class, 'sources'])->name('sources');
    Route::get('/categories', [ArticleController::class, 'categories'])->name('categories');
    Route::get('/authors', [ArticleController::class, 'authors'])->name('authors');
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // User preferences
    Route::prefix('preferences')->name('preferences.')->group(function () {
        Route::get('/', [PreferenceController::class, 'show'])->name('show');
        Route::post('/', [PreferenceController::class, 'store'])->name('store');
        Route::delete('/', [PreferenceController::class, 'destroy'])->name('destroy');
    });
});
