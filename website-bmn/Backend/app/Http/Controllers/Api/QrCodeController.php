<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeController extends Controller
{
    /**
     * Scan barang by kode (public endpoint).
     */
    public function scan(string $kode): JsonResponse
    {
        $barang = Barang::with(['kategori', 'lokasi', 'ruangan', 'gedung', 'direktorat', 'histori.user'])
            ->where('kode_barang', $kode)
            ->first();

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barang ditemukan',
            'data' => [
                'kode_barang' => $barang->kode_barang,
                'nama' => $barang->nama,
                'merek' => $barang->merek,
                'kategori' => $barang->kategori?->nama,
                'lokasi' => $barang->lokasi?->nama,
                'ruangan' => $barang->ruangan?->nama,
                'gedung' => $barang->gedung?->nama,
                'kondisi' => $barang->kondisi,
                'status' => $barang->status,
                'histori' => $barang->histori->take(10)->map(function ($h) {
                    return [
                        'tanggal' => $h->terjadi_pada,
                        'tipe' => $h->tipe,
                        'deskripsi' => $h->deskripsi,
                        'user' => $h->user?->name,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Generate QR code for a barang (authenticated).
     */
    public function generate(int $id): JsonResponse
    {
        $barang = Barang::findOrFail($id);

        $url = config('app.url') . '/barang/scan/' . $barang->kode_barang;

        // Generate QR as base64
        $qrCode = QrCode::size(300)->generate($url);
        $base64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);

        return response()->json([
            'success' => true,
            'message' => 'QR code generated',
            'data' => [
                'barang_id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama' => $barang->nama,
                'qr_url' => $url,
                'qr_image_base64' => $base64,
            ],
        ]);
    }
}
