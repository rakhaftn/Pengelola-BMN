<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengembalian extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'pengembalian';

    protected $fillable = [
        'nomor_pengembalian', 'peminjaman_id', 'diterima_oleh',
        'tanggal_pengembalian', 'kondisi_barang', 'ada_kerusakan', 'catatan',
    ];

    protected $casts = [
        'tanggal_pengembalian' => 'date',
        'ada_kerusakan'        => 'boolean',
    ];

    public static function generateNomor(): string
    {
        $year = now()->year;
        $prefix = "KMB-{$year}-";
        $last = static::withTrashed()
            ->where('nomor_pengembalian', 'like', $prefix . '%')
            ->orderByDesc('nomor_pengembalian')
            ->value('nomor_pengembalian');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }
}
