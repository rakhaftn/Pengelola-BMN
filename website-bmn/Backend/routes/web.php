<?php

use App\Http\Controllers\BarangScanController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\StockOpnameScanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// SPA - React Admin Panel (catch-all must be last)
Route::get('/', function () {
    // If authenticated, go to dashboard, else go to login
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// React SPA catch-all route
Route::get('/{any}', function () {
    return view('spa.index');
})->where('any', '^(?!api).*$');

// Public QR scan landing page
Route::get('/barang/scan/{kode}', [BarangScanController::class, 'show'])->name('barang.scan');
Route::get('/barang/qr/{kode}/download', [BarangScanController::class, 'downloadQr'])->name('barang.qr.download');

// Document generation (PDF) - requires authenticated
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dokumen/form-peminjaman/{peminjaman}', [DokumenController::class, 'formPeminjaman'])->name('dokumen.form-peminjaman');
    Route::get('/dokumen/bast/{peminjaman}', [DokumenController::class, 'bast'])->name('dokumen.bast');
    Route::get('/dokumen/ba-pengembalian/{pengembalian}', [DokumenController::class, 'baPengembalian'])->name('dokumen.ba-pengembalian');
    Route::get('/dokumen/surat-pernyataan/{peminjaman}', [DokumenController::class, 'suratPernyataan'])->name('dokumen.surat-pernyataan');
    Route::get('/dokumen/surat-pengembalian/{pengembalian}', [DokumenController::class, 'suratPengembalian'])->name('dokumen.surat-pengembalian');

    // Stock Opname
    Route::get('/stock-opname/{stockOpname}/scan', [StockOpnameScanController::class, 'show'])->name('stock-opname.scan');
    Route::post('/stock-opname/{stockOpname}/scan', [StockOpnameScanController::class, 'scan'])->name('stock-opname.scan.process');

    // Export
    Route::get('/export/barang', [ExportController::class, 'barang'])->name('export.barang');
    Route::get('/export/peminjaman', [ExportController::class, 'peminjaman'])->name('export.peminjaman');

    // Import
    Route::post('/import/barang', [ImportController::class, 'barang'])->name('import.barang');
    Route::post('/import/pegawai', [ImportController::class, 'pegawai'])->name('import.pegawai');
});
