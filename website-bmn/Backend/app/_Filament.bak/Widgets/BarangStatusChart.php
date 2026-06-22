<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\DoughnutChartWidget;

class BarangStatusChart extends DoughnutChartWidget
{
    protected static ?string $heading = 'Status Barang';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = null;

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        // Ambil data barang berdasarkan status
        $statusCounts = Barang::where('tahun_perolehan', $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Mapping status ke label Indonesia
        $statusLabels = Barang::STATUS;

        // Siapkan data dengan label Indonesia
        $labels = [];
        $data = [];
        foreach ($statusLabels as $key => $label) {
            $labels[] = $label;
            $data[] = $statusCounts[$key] ?? 0;
        }

        // Jika semua 0, tampilkan placeholder
        if (array_sum($data) === 0) {
            $labels = ['Tidak ada data'];
            $data = [1];
        }

        // Warna untuk doughnut chart
        $colors = [
            '#10b981', // tersedia - success
            '#3b82f6', // dipinjam - info
            '#f59e0b', // perbaikan - warning
            '#ef4444', // hilang - danger
            '#6b7280', // dihapuskan - gray
        ];

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
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