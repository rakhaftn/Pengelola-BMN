<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'barang';

    protected $fillable = [
        'kode_barang', 'nama', 'kategori_id',
        'direktorat_id', 'gedung_id', 'lantai_id', 'lokasi_id', 'ruangan_id',
        'merek', 'nomor_seri', 'tahun_perolehan', 'nilai_perolehan',
        'kondisi', 'status', 'keterangan', 'foto',
    ];

    protected $casts = [
        'nilai_perolehan'  => 'decimal:2',
        'tahun_perolehan'  => 'integer',
    ];

    public const KONDISI = [
        'baik'         => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat'  => 'Rusak Berat',
    ];

    public const STATUS = [
        'pengadaan'       => 'Pengadaan',
        'tersedia'        => 'Tersedia',
        'dipinjam'        => 'Dipinjam',
        'dalam_perawatan' => 'Dalam Perawatan',
        'rusak_ringan'    => 'Rusak Ringan',
        'rusak_berat'     => 'Rusak Berat',
        'hilang'          => 'Hilang',
        'dihapuskan'      => 'Dihapuskan',
        'dimusnahkan'     => 'Dimusnahkan',
    ];

    /**
     * Generate kode barang otomatis: BMN-2026-000001
     */
    public static function generateKode(): string
    {
        $year = now()->year;
        $prefix = "BMN-{$year}-";
        $last = static::withTrashed()
            ->where('kode_barang', 'like', $prefix . '%')
            ->orderByDesc('kode_barang')
            ->value('kode_barang');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBarang::class, 'kategori_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class, 'direktorat_id');
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }

    public function lantai(): BelongsTo
    {
        return $this->belongsTo(Lantai::class, 'lantai_id');
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(QrCode::class, 'barang_id');
    }

    public function histori(): HasMany
    {
        return $this->hasMany(BarangHistori::class, 'barang_id')->latest('terjadi_pada');
    }

    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'barang_id');
    }
}
