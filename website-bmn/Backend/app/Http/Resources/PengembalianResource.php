<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengembalianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor_pengembalian' => $this->nomor_pengembalian,
            'tanggal_pengembalian' => $this->tanggal_pengembalian?->toDateString(),
            'kondisi_barang' => $this->kondisi_barang,
            'ada_kerusakan' => $this->ada_kerusakan,
            'catatan' => $this->catatan,

            // Relations
            'peminjaman' => $this->whenLoaded('peminjaman', function () {
                return $this->peminjaman ? [
                    'id' => $this->peminjaman->id,
                    'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
                    'status' => $this->peminjaman->status,
                    'tanggal_pinjam' => $this->peminjaman->tanggal_pinjam?->toDateString(),
                    'tanggal_kembali_rencana' => $this->peminjaman->tanggal_kembali_rencana?->toDateString(),
                    'peminjam' => $this->peminjaman->peminjam ? [
                        'id' => $this->peminjaman->peminjam->id,
                        'name' => $this->peminjaman->peminjam->name,
                        'nip' => $this->peminjaman->peminjam->nip,
                    ] : null,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
