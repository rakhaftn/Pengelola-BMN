<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lantai extends Model
{
    use SoftDeletes;

    protected $table = 'lantais';

    protected $fillable = [
        'gedung_id', 'kode', 'nama', 'lantai_ke', 'keterangan', 'is_active',
    ];

    protected $casts = [
        'lantai_ke' => 'integer',
    ];

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }

    public function lokasis(): HasMany
    {
        return $this->hasMany(Lokasi::class, 'lantai_id');
    }

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'lantai_id');
    }
}
