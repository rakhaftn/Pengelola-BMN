<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPeminjamanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jumlah' => $this->jumlah,
            'kondisi_kembali' => $this->kondisi_kembali,
            'created_at' => $this->created_at?->toIso8601String(),

            // Relations
            'barang' => $this->whenLoaded('barang', function () {
                return $this->barang ? [
                    'id' => $this->barang->id,
                    'kode_barang' => $this->barang->kode_barang,
                    'nama' => $this->barang->nama,
                    'merek' => $this->barang->merek,
                    'kategori' => $this->barang->kategori?->nama,
                    'status' => $this->barang->status,
                    'kondisi' => $this->barang->kondisi,
                ] : null;
            }),
            'peminjaman' => $this->whenLoaded('peminjaman', function () {
                return $this->peminjaman ? [
                    'id' => $this->peminjaman->id,
                    'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
                    'status' => $this->peminjaman->status,
                ] : null;
            }),
        ];
    }
}
