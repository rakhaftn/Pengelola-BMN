<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Direktorat extends Model
{
    use SoftDeletes;

    protected $table = 'direktorats';

    protected $fillable = [
        'kode', 'nama', 'kepala', 'keterangan', 'is_active',
    ];

    public function gedungs(): HasMany
    {
        return $this->hasMany(Gedung::class, 'direktorat_id');
    }

    public function barangs(): HasMany
    {
        return $this->hasMany(Barang::class, 'direktorat_id');
    }
}
