<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriBarangController;
use App\Http\Controllers\Api\LokasiController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PengembalianController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\UnitKerjaController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| TRASET API for Mobile App Integration
| Authentication: Laravel Sanctum (token-based)
|
*/

// ==================== PUBLIC ROUTES ====================

// QR Code lookup (public - no auth required)
Route::get('/barang/scan/{kode}', [QrCodeController::class, 'scan']);

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

    // ===== BARANG (Barang Milik Negara) =====
    Route::apiResource('barang', BarangController::class);
    Route::get('/barang/kode/{kode}', [BarangController::class, 'findByKode']);
    Route::get('/barang/{id}/qr', [QrCodeController::class, 'generate']);
    Route::get('/barang/{id}/histori', [BarangController::class, 'histori']);

    // ===== PEMINJAMAN =====
    Route::apiResource('peminjaman', PeminjamanController::class);

    // Workflow actions
    Route::post('/peminjaman/{id}/submit', [PeminjamanController::class, 'submit']);
    Route::put('/peminjaman/{id}/approve', [PeminjamanController::class, 'approve']);
    Route::put('/peminjaman/{id}/approve-staff', [PeminjamanController::class, 'approveStaff']);
    Route::put('/peminjaman/{id}/reject', [PeminjamanController::class, 'reject']);
    Route::post('/peminjaman/{id}/serah-terima', [PeminjamanController::class, 'serahTerima']);
    Route::post('/peminjaman/{id}/cancel', [PeminjamanController::class, 'cancel']);

    // Detail peminjaman (barang yang dipinjam)
    Route::get('/peminjaman/{id}/details', [PeminjamanController::class, 'details']);
    Route::post('/peminjaman/{id}/details', [PeminjamanController::class, 'addDetail']);
    Route::delete('/peminjaman/{peminjaman}/details/{detail}', [PeminjamanController::class, 'removeDetail']);

    // ===== PENGEMBALIAN =====
    Route::apiResource('pengembalian', PengembalianController::class);
    Route::get('/peminjaman/{peminjaman}/pengembalian', [PengembalianController::class, 'byPeminjaman']);

    // ===== MASTER DATA - LOKASI =====
    Route::prefix('lokasi')->group(function () {
        Route::get('/direktorat', [LokasiController::class, 'direktorat']);
        Route::get('/gedung', [LokasiController::class, 'gedung']);
        Route::get('/lantai', [LokasiController::class, 'lantai']);
        Route::get('/ruangan', [LokasiController::class, 'ruangan']);
        Route::get('/unit-kerja', [LokasiController::class, 'unitKerja']);
    });

    // ===== KATEGORI BARANG =====
    Route::get('/kategori-barang', [KategoriBarangController::class, 'index']);
    Route::get('/kategori-barang/{id}', [KategoriBarangController::class, 'show']);
    Route::post('/kategori-barang', [KategoriBarangController::class, 'store']);
    Route::put('/kategori-barang/{id}', [KategoriBarangController::class, 'update']);
    Route::delete('/kategori-barang/{id}', [KategoriBarangController::class, 'destroy']);

    // ===== UNIT KERJA =====
    Route::get('/unit-kerja', [UnitKerjaController::class, 'index']);
    Route::get('/unit-kerja/{id}', [UnitKerjaController::class, 'show']);
    Route::post('/unit-kerja', [UnitKerjaController::class, 'store']);
    Route::put('/unit-kerja/{id}', [UnitKerjaController::class, 'update']);
    Route::delete('/unit-kerja/{id}', [UnitKerjaController::class, 'destroy']);

    // ===== LOKASI (full CRUD) =====
    Route::get('/lokasi', [LokasiController::class, 'index']);
    Route::post('/lokasi', [LokasiController::class, 'store']);
    Route::put('/lokasi/{id}', [LokasiController::class, 'update']);
    Route::delete('/lokasi/{id}', [LokasiController::class, 'destroy']);

    // ===== USER MANAGEMENT =====
    Route::apiResource('users', UserController::class);
    Route::put('/users/{id}/role', [UserController::class, 'updateRole']);

    // ===== AUDIT LOG (read only) =====
    Route::get('/audit-logs', [DashboardController::class, 'auditLogs']);
});
