<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_barang' => $this->kode_barang,
            'nama' => $this->nama,
            'merek' => $this->merek,
            'nomor_seri' => $this->nomor_seri,
            'tahun_perolehan' => $this->tahun_perolehan,
            'nilai_perolehan' => $this->nilai_perolehan,
            'kondisi' => $this->kondisi,
            'kondisi_label' => $this->kondisi ? Barang::KONDISI[$this->kondisi] ?? $this->kondisi : null,
            'status' => $this->status,
            'status_label' => $this->status ? Barang::STATUS[$this->status] ?? $this->status : null,
            'keterangan' => $this->keterangan,
            'foto' => $this->foto ? asset('storage/' . $this->foto) : null,
            'qr_url' => config('app.url') . '/barang/scan/' . $this->kode_barang,

            // Relations
            'kategori' => $this->whenLoaded('kategori', function () {
                return [
                    'id' => $this->kategori->id,
                    'nama' => $this->kategori->nama,
                    'kode' => $this->kategori->kode,
                ];
            }),
            'lokasi' => $this->whenLoaded('lokasi', function () {
                return $this->lokasi ? [
                    'id' => $this->lokasi->id,
                    'nama' => $this->lokasi->nama,
                ] : null;
            }),
            'ruangan' => $this->whenLoaded('ruangan', function () {
                return $this->ruangan ? [
                    'id' => $this->ruangan->id,
                    'nama' => $this->ruangan->nama,
                ] : null;
            }),
            'gedung' => $this->whenLoaded('gedung', function () {
                return $this->gedung ? [
                    'id' => $this->gedung->id,
                    'nama' => $this->gedung->nama,
                ] : null;
            }),
            'direktorat' => $this->whenLoaded('direktorat', function () {
                return $this->direktorat ? [
                    'id' => $this->direktorat->id,
                    'nama' => $this->direktorat->nama,
                ] : null;
            }),
            'lantai' => $this->whenLoaded('lantai', function () {
                return $this->lantai ? [
                    'id' => $this->lantai->id,
                    'nama' => $this->lantai->nama,
                ] : null;
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
