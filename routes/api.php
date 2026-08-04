<?php

use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PublicArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Guest Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('google/redirect', [AuthController::class, 'googleRedirect']);
        Route::get('google/callback', [AuthController::class, 'googleCallback']);
        Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
    });

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Admin User Management
        Route::prefix('admin/users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::delete('/{user}', [AdminUserController::class, 'destroy']);
            Route::put('/{user}/role', [AdminUserController::class, 'updateRole']);
            Route::post('/{user}/permissions', [AdminUserController::class, 'updatePermissions']);
        });

        // Authenticated Article Management
        Route::prefix('articles')->group(function () {
            Route::get('/manage', [ArticleController::class, 'index']);
            Route::post('/', [ArticleController::class, 'store']);
            Route::get('/manage/{id}', [ArticleController::class, 'show']);
            Route::put('/{id}', [ArticleController::class, 'update']);
            Route::patch('/{id}/archive', [ArticleController::class, 'archive']);
            Route::delete('/{id}', [ArticleController::class, 'destroy']);
        });
    });

    // Public Article Endpoints
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{idOrSlug}', [PublicArticleController::class, 'show']);
});
