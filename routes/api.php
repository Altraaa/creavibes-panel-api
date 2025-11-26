<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerController;

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

// Public routes (no authentication required)
Route::prefix('volunteers')->group(function () {
    Route::post('/', [VolunteerController::class, 'store']); // Volunteer registration
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refreshToken']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Volunteer management routes (protected - admin/staff only)
    Route::prefix('volunteers')->group(function () {
        Route::get('/', [VolunteerController::class, 'index']);
        Route::get('/{id}', [VolunteerController::class, 'show']);
        Route::put('/{id}', [VolunteerController::class, 'update']);
        Route::delete('/{id}', [VolunteerController::class, 'destroy']);
    });
});