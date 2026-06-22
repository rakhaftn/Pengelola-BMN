<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lokasi extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'lokasi';

    protected $fillable = ['lantai_id', 'kode', 'nama', 'alamat', 'keterangan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function lantai(): BelongsTo
    {
        return $this->belongsTo(Lantai::class, 'lantai_id');
    }

    public function ruangan(): HasMany
    {
        return $this->hasMany(Ruangan::class, 'lokasi_id');
    }

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'lokasi_id');
    }
}
