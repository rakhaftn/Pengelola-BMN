<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangHistori extends Model
{
    protected $table = 'barang_histori';

    protected $fillable = [
        'barang_id', 'user_id', 'tipe', 'judul', 'deskripsi',
        'kondisi_sebelum', 'kondisi_sesudah',
        'status_sebelum', 'status_sesudah', 'terjadi_pada',
    ];

    protected $casts = ['terjadi_pada' => 'datetime'];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
