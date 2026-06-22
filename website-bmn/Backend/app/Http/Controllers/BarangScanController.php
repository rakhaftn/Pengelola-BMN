<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Services\QrCodeService;
use Illuminate\Support\Facades\Storage;

class BarangScanController extends Controller
{
    /**
     * Public landing page when a QR code is scanned.
     */
    public function show(string $kode, QrCodeService $qrService)
    {
        $barang = Barang::with(['kategori', 'lokasi', 'ruangan', 'histori.user'])
            ->where('kode_barang', $kode)
            ->firstOrFail();

        $qrSvg = $qrService->inlineSvg(route('barang.scan', ['kode' => $barang->kode_barang]), 160);

        return view('barang.scan', compact('barang', 'qrSvg'));
    }

    /**
     * Download QR code SVG for printing/labeling.
     */
    public function downloadQr(string $kode, QrCodeService $qrService)
    {
        $barang = Barang::where('kode_barang', $kode)->firstOrFail();

        if (! $barang->qrCode || ! Storage::disk('public')->exists($barang->qrCode->path)) {
            $qrService->generateForBarang($barang);
            $barang->refresh();
        }

        return Storage::disk('public')->download($barang->qrCode->path, $barang->kode_barang . '.svg');
    }
}
