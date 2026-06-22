<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function formPeminjaman(Peminjaman $peminjaman)
    {
        $peminjaman->load(['peminjam.unitKerja', 'details.barang', 'approvedAtasan', 'approvedPetugas', 'unitKerja']);
        return Pdf::loadView('pdf.form-peminjaman', compact('peminjaman'))
            ->setPaper('a4', 'portrait');
    }

    public function beritaAcaraSerahTerima(Peminjaman $peminjaman)
    {
        $peminjaman->load(['peminjam.unitKerja', 'details.barang', 'approvedPetugas']);
        return Pdf::loadView('pdf.bast', compact('peminjaman'))
            ->setPaper('a4', 'portrait');
    }

    public function beritaAcaraPengembalian(Pengembalian $pengembalian)
    {
        $pengembalian->load(['peminjaman.peminjam', 'peminjaman.details.barang', 'diterimaOleh']);
        return Pdf::loadView('pdf.ba-pengembalian', compact('pengembalian'))
            ->setPaper('a4', 'portrait');
    }

    public function suratPernyataan(Peminjaman $peminjaman)
    {
        $peminjaman->load(['peminjam.unitKerja', 'details.barang', 'approvedAtasan', 'unitKerja']);
        return Pdf::loadView('pdf.surat-pernyataan', compact('peminjaman'))
            ->setPaper('a4', 'portrait');
    }

    public function suratPengembalian(Pengembalian $pengembalian)
    {
        $pengembalian->load(['peminjaman.peminjam', 'peminjaman.details.barang', 'diterimaOleh']);
        return Pdf::loadView('pdf.surat-pengembalian', compact('pengembalian'))
            ->setPaper('a4', 'portrait');
    }
}
