<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangHistori;
use Illuminate\Support\Facades\Auth;

class BarangHistoriService
{
    public function catat(Barang $barang, string $tipe, string $judul, array $extra = []): BarangHistori
    {
        return BarangHistori::create(array_merge([
            'barang_id'    => $barang->id,
            'user_id'      => Auth::id(),
            'tipe'         => $tipe,
            'judul'        => $judul,
            'terjadi_pada' => now(),
        ], $extra));
    }
}
