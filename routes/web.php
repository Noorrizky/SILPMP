<?php

use App\Http\Controllers\PatientAuthController;
use App\Http\Controllers\PatientDashboardController;
use Illuminate\Support\Facades\Route;
// --- TAMBAHKAN BARIS INI ---
use App\Http\Controllers\AdminResultController;

// Halaman Depan = Login Pasien
Route::get('/', [PatientAuthController::class, 'showLoginForm'])->name('login');
Route::post('/', [PatientAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [PatientAuthController::class, 'logout'])->name('logout');

// Halaman Khusus Pasien (Harus Login)
Route::middleware('auth:patient')->group(function () {
    Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('patient.dashboard');
    Route::get('/print/{id}', [PatientDashboardController::class, 'print'])->name('patient.print');
});

// ROUTE KHUSUS ADMIN (Tambahkan ini)
Route::middleware('auth')->get('/admin/print-result/{record}', [AdminResultController::class, 'print'])
    ->name('admin.print.result');