<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'nomor_opname', 'user_id', 'tanggal_opname', 'lokasi_id',
        'status', 'catatan', 'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_opname' => 'date',
        'tanggal_selesai' => 'datetime',
    ];

    public const STATUS = [
        'draft'     => 'Draft',
        'berlangsung' => 'Berlangsung',
        'selesai'   => 'Selesai',
        'dicancel'  => 'Dibatalkan',
    ];

    /**
     * Generate nomor opname: SO-2026-000001
     */
    public static function generateNomor(): string
    {
        $year = now()->year;
        $prefix = "SO-{$year}-";
        $last = static::withTrashed()
            ->where('nomor_opname', 'like', $prefix . '%')
            ->orderByDesc('nomor_opname')
            ->value('nomor_opname');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;
        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class, 'opname_id');
    }
}
