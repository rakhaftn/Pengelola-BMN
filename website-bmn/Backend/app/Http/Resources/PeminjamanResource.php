<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor_peminjaman' => $this->nomor_peminjaman,
            'status' => $this->status,
            'status_label' => $this->status ? \App\Models\Peminjaman::STATUS[$this->status] ?? $this->status : null,
            'tanggal_pinjam' => $this->tanggal_pinjam?->toDateString(),
            'tanggal_kembali_rencana' => $this->tanggal_kembali_rencana?->toDateString(),
            'tanggal_kembali_aktual' => $this->tanggal_kembali_aktual?->toDateString(),
            'tujuan' => $this->tujuan,
            'keperluan' => $this->keperluan,
            'catatan' => $this->catatan,
            'alasan_penolakan' => $this->alasan_penolakan,

            // Approval info
            'approved_atasan_at' => $this->approved_atasan_at?->toIso8601String(),
            'approved_petugas_at' => $this->approved_petugas_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),

            // Document URLs
            'dokumen_atasan_url' => $this->dokumen_atasan ? asset('storage/' . $this->dokumen_atasan) : null,
            'dokumen_petugas_url' => $this->dokumen_petugas ? asset('storage/' . $this->dokumen_petugas) : null,

            // Relations
            'peminjam' => $this->whenLoaded('peminjam', function () {
                return new UserResource($this->peminjam);
            }),
            'unit_kerja' => $this->whenLoaded('unitKerja', function () {
                return $this->unitKerja ? [
                    'id' => $this->unitKerja->id,
                    'nama' => $this->unitKerja->nama,
                    'kode' => $this->unitKerja->kode,
                ] : null;
            }),
            'approved_atasan' => $this->whenLoaded('approvedAtasan', function () {
                return $this->approvedAtasan ? [
                    'id' => $this->approvedAtasan->id,
                    'name' => $this->approvedAtasan->name,
                ] : null;
            }),
            'approved_petugas' => $this->whenLoaded('approvedPetugas', function () {
                return $this->approvedPetugas ? [
                    'id' => $this->approvedPetugas->id,
                    'name' => $this->approvedPetugas->name,
                ] : null;
            }),
            'details' => $this->whenLoaded('details', function () {
                return DetailPeminjamanResource::collection($this->details);
            }),
            'pengembalian' => $this->whenLoaded('pengembalian', function () {
                return $this->pengembalian ? new PengembalianResource($this->pengembalian) : null;
            }),

            // Summary counts
            'total_barang' => $this->when($this->relationLoaded('details'), function () {
                return $this->details->sum('jumlah');
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
