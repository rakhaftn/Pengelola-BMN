<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peminjaman extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'peminjaman';

    protected $fillable = [
        'nomor_peminjaman', 'peminjam_id', 'unit_kerja_id', 'status',
        'tanggal_pinjam', 'tanggal_kembali_rencana', 'tanggal_kembali_aktual',
        'tujuan', 'keperluan',
        'approved_atasan_id', 'approved_atasan_at', 'dokumen_atasan',
        'approved_petugas_id', 'approved_petugas_at', 'dokumen_petugas',
        'rejected_by', 'rejected_at', 'alasan_penolakan', 'catatan',
    ];

    protected $casts = [
        'tanggal_pinjam'          => 'date',
        'tanggal_kembali_rencana' => 'date',
        'tanggal_kembali_aktual'  => 'date',
        'approved_atasan_at'      => 'datetime',
        'approved_petugas_at'     => 'datetime',
        'rejected_at'             => 'datetime',
    ];

    public const STATUS = [
        'draft'                => 'Draft',
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'disetujui'            => 'Disetujui',
        'ditolak'              => 'Ditolak',
        'dipinjam'             => 'Dipinjam',
        'dikembalikan'         => 'Dikembalikan',
        'selesai'              => 'Selesai',
    ];

    /**
     * Generate nomor peminjaman: PJM-2026-000001
     */
    public static function generateNomor(): string
    {
        $year = now()->year;
        $prefix = "PJM-{$year}-";
        $last = static::withTrashed()
            ->where('nomor_peminjaman', 'like', $prefix . '%')
            ->orderByDesc('nomor_peminjaman')
            ->value('nomor_peminjaman');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function peminjam(): BelongsTo
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function approvedAtasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_atasan_id');
    }

    public function approvedPetugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_petugas_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'peminjaman_id');
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'peminjaman_id');
    }
}
