<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ruangan extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'ruangan';

    protected $fillable = ['lokasi_id', 'kode', 'nama', 'lantai', 'keterangan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'ruangan_id');
    }
}
