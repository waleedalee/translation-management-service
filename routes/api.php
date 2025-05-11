<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TranslationController;
use App\Http\Controllers\API\AuthController;

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

// Public authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Translation Management Service Routes
    Route::prefix('translations')->group(function () {
        // Special operations - must be defined before the parameterized routes
        Route::get('/export/json', [TranslationController::class, 'export']);
        Route::post('/search', [TranslationController::class, 'search']);
        
        // CRUD operations
        Route::get('/', [TranslationController::class, 'index']);
        Route::post('/', [TranslationController::class, 'store']);
        Route::get('/{id}', [TranslationController::class, 'show']);
        Route::put('/{id}', [TranslationController::class, 'update']);
        Route::delete('/{id}', [TranslationController::class, 'destroy']);
    });
}); 