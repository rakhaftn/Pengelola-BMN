<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriBarang extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'kategori_barang';

    protected $fillable = ['kode', 'nama', 'keterangan', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function barang(): HasMany
    {
        return $this->hasMany(Barang::class, 'kategori_id');
    }
}
