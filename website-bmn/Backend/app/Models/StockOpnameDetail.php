<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetail extends Model
{
    protected $table = 'stock_opname_details';

    protected $fillable = [
        'opname_id', 'barang_id', 'status', 'kondisi_sebelum',
        'kondisi_sesudah', 'foto', 'catatan', 'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public const STATUS_SCAN = [
        'ditemukan'      => 'Ditemukan',
        'tidak_ditemukan' => 'Tidak Ditemukan',
        'rusak'          => 'Rusak',
    ];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
