<?php

use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes (publik)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Auth routes (butuh JWT)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me',       [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Admin routes — butuh JWT + role admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    // Manajemen verifikasi penyewa
    Route::get('users',              [UserVerificationController::class, 'index']);
    Route::get('users/{id}',         [UserVerificationController::class, 'show']);
    Route::post('users/{id}/approve', [UserVerificationController::class, 'approve']);
    Route::post('users/{id}/reject',  [UserVerificationController::class, 'reject']);
});
