<?php

use App\Http\Controllers\Admin\GarageController as AdminGarageController;
use App\Http\Controllers\Admin\RentalController as AdminRentalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Penyewa\RentalController as PenyewaRentalController;
use App\Http\Controllers\Penyewa\VehicleController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes (guest only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/',          [WebAuthController::class, 'showLogin']);
    Route::get('/login',     [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [WebAuthController::class, 'login']);
    Route::get('/register',  [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Penyewa routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('penyewa')->name('penyewa.')->group(function () {
    Route::get('dashboard', fn () => view('penyewa.dashboard'))->name('dashboard');

    // Kendaraan
    Route::get('vehicles',               [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('vehicles/create',        [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('vehicles',              [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('vehicles/{id}/edit',     [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('vehicles/{id}',          [VehicleController::class, 'update'])->name('vehicles.update');

    // Sewa
    Route::get('rentals',                [PenyewaRentalController::class, 'index'])->name('rentals.index');
    Route::get('rentals/create',         [PenyewaRentalController::class, 'create'])->name('rentals.create');
    Route::post('rentals/preview',       [PenyewaRentalController::class, 'preview'])->name('rentals.preview');
    Route::post('rentals',               [PenyewaRentalController::class, 'store'])->name('rentals.store');
    Route::get('rentals/{id}',           [PenyewaRentalController::class, 'show'])->name('rentals.show');
});

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // Kelola penyewa & verifikasi
    Route::get('users',                   [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{id}',              [AdminUserController::class, 'show'])->name('users.show');
    Route::post('users/{id}/approve',     [AdminUserController::class, 'approve'])->name('users.approve');
    Route::post('users/{id}/reject',      [AdminUserController::class, 'reject'])->name('users.reject');

    // Garasi
    Route::get('garages',                 [AdminGarageController::class, 'index'])->name('garages.index');
    Route::get('garages/create',          [AdminGarageController::class, 'create'])->name('garages.create');
    Route::post('garages',                [AdminGarageController::class, 'store'])->name('garages.store');
    Route::get('garages/{id}/edit',       [AdminGarageController::class, 'edit'])->name('garages.edit');
    Route::put('garages/{id}',            [AdminGarageController::class, 'update'])->name('garages.update');

    // Transaksi sewa
    Route::get('rentals',                 [AdminRentalController::class, 'index'])->name('rentals.index');
    Route::get('rentals/{id}',            [AdminRentalController::class, 'show'])->name('rentals.show');
    Route::get('rentals/{id}/extend',     [AdminRentalController::class, 'extendForm'])->name('rentals.extend.form');
    Route::post('rentals/{id}/extend',    [AdminRentalController::class, 'extend'])->name('rentals.extend');

    // Laporan
    Route::get('reports',                 [ReportController::class, 'index'])->name('reports.index');
});
