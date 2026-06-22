<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Services\PdfService;

class DokumenController extends Controller
{
    public function __construct(private PdfService $pdf) {}

    public function formPeminjaman(Peminjaman $peminjaman)
    {
        return $this->pdf->formPeminjaman($peminjaman)
            ->stream('form-peminjaman-' . $peminjaman->nomor_peminjaman . '.pdf');
    }

    public function bast(Peminjaman $peminjaman)
    {
        return $this->pdf->beritaAcaraSerahTerima($peminjaman)
            ->stream('bast-' . $peminjaman->nomor_peminjaman . '.pdf');
    }

    public function baPengembalian(Pengembalian $pengembalian)
    {
        return $this->pdf->beritaAcaraPengembalian($pengembalian)
            ->stream('ba-pengembalian-' . $pengembalian->nomor_pengembalian . '.pdf');
    }

    public function suratPernyataan(Peminjaman $peminjaman)
    {
        $nomor = "SP/{$peminjaman->nomor_peminjaman}/BMN/PP.1/" . now()->format('Y');
        return $this->pdf->suratPernyataan($peminjaman)
            ->stream('surat-pernyataan-' . $peminjaman->nomor_peminjaman . '.pdf');
    }

    public function suratPengembalian(Pengembalian $pengembalian)
    {
        $nomor = $pengembalian->nomor_pengembalian ?? 'SP-' . $pengembalian->peminjaman->nomor_peminjaman;
        return $this->pdf->suratPengembalian($pengembalian)
            ->stream('surat-pengembalian-' . $nomor . '.pdf');
    }
}
