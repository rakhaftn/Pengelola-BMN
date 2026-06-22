<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangExport
{
    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="barang-' . date('Y-m-d') . '.csv"',
            'Cache-Control' => 'no-store, no-cache',
        ];

        $columns = ['Kode', 'Nama', 'Kategori', 'Merek', 'No Seri', 'Tahun', 'Nilai', 'Kondisi', 'Status', 'Lokasi', 'Ruangan', 'Direktorat', 'Gedung', 'Lantai', 'Dibuat'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
            fputcsv($file, $columns);

            Barang::with(['kategori', 'lokasi', 'ruangan', 'direktorat', 'gedung', 'lantai'])
                ->orderBy('kode_barang')
                ->chunk(500, function ($barangs) use ($file) {
                    foreach ($barangs as $barang) {
                        fputcsv($file, [
                            $barang->kode_barang,
                            $barang->nama,
                            $barang->kategori->nama ?? '-',
                            $barang->merek ?? '-',
                            $barang->nomor_seri ?? '-',
                            $barang->tahun_perolehan,
                            $barang->nilai_perolehan,
                            Barang::KONDISI[$barang->kondisi] ?? $barang->kondisi,
                            Barang::STATUS[$barang->status] ?? $barang->status,
                            $barang->lokasi->nama ?? '-',
                            $barang->ruangan->nama ?? '-',
                            $barang->direktorat->nama ?? '-',
                            $barang->gedung->nama ?? '-',
                            $barang->lantai->nama ?? '-',
                            $barang->created_at->format('d/m/Y'),
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}