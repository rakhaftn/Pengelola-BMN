<?php

namespace App\Exports;

use App\Models\Peminjaman;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PeminjamanExport
{
    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="peminjaman-' . date('Y-m-d') . '.csv"',
            'Cache-Control' => 'no-store, no-cache',
        ];

        $columns = ['Nomor', 'Peminjam', 'Unit Kerja', 'Status', 'Tgl Pinjam', 'Tgl Rencana Kembali', 'Tgl Kembali Aktual', 'Jml Barang', 'Keperluan', 'Dibuat'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
            fputcsv($file, $columns);

            Peminjaman::with(['peminjam', 'unitKerja', 'details'])
                ->orderBy('created_at', 'desc')
                ->chunk(500, function ($peminjamans) use ($file) {
                    foreach ($peminjamans as $p) {
                        fputcsv($file, [
                            $p->nomor_peminjaman,
                            $p->peminjam->name ?? '-',
                            $p->unitKerja->nama ?? '-',
                            Peminjaman::STATUS[$p->status] ?? $p->status,
                            $p->tanggal_pinjam?->format('d/m/Y') ?? '-',
                            $p->tanggal_kembali_rencana?->format('d/m/Y') ?? '-',
                            $p->tanggal_kembali_aktual?->format('d/m/Y') ?? '-',
                            $p->details->count(),
                            $p->keperluan ?? '-',
                            $p->created_at->format('d/m/Y H:i'),
                        ]);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}