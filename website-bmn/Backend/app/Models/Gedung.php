<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gedung extends Model
{
    use SoftDeletes;

    protected $table = 'gedungs';

    protected $fillable = [
        'direktorat_id', 'kode', 'nama', 'alamat', 'keterangan', 'is_active',
    ];

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class, 'direktorat_id');
    }

    public function lantais(): HasMany
    {
        return $this->hasMany(Lantai::class, 'gedung_id');
    }

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'gedung_id');
    }
}
