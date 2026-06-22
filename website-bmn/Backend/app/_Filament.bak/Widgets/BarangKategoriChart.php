<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\KategoriBarang;
use Filament\Widgets\PieChartWidget;
use Illuminate\Support\Facades\DB;

class BarangKategoriChart extends PieChartWidget
{
    protected static ?string $heading = 'Distribusi Barang per Kategori';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = null;

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        // Ambil data barang berdasarkan kategori
        $data = DB::table('barang')
            ->join('kategori_barang', 'barang.kategori_id', '=', 'kategori_barang.id')
            ->where('barang.tahun_perolehan', $year)
            ->select('kategori_barang.nama as label', DB::raw('COUNT(barang.id) as total'))
            ->groupBy('kategori_barang.nama')
            ->pluck('total', 'label')
            ->toArray();

        // Jika tidak ada data, tampilkan placeholder
        if (empty($data)) {
            $data = ['Tidak ada data' => 1];
        }

        // Warna untuk pie chart
        $colors = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16',
        ];

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        $years = range(now()->year - 2, now()->year);
        return array_combine($years, $years);
    }
}