<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use Filament\Widgets\BarChartWidget;

class PeminjamanStatusChart extends BarChartWidget
{
    protected static ?string $heading = 'Peminjaman per Status';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = null;

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        // Ambil data peminjaman berdasarkan status untuk tahun yang dipilih
        $statusCounts = Peminjaman::whereYear('tanggal_pinjam', $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Mapping status ke label Indonesia
        $statusLabels = Peminjaman::STATUS;

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
            $data = [0];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Peminjaman',
                    'data' => $data,
                    'backgroundColor' => [
                        '#6b7280', // draft - gray
                        '#f59e0b', // menunggu_persetujuan - warning
                        '#3b82f6', // disetujui - info
                        '#ef4444', // ditolak - danger
                        '#8b5cf6', // dipinjam - purple
                        '#10b981', // dikembalikan - success
                        '#22c55e', // selesai - green
                    ],
                    'borderRadius' => 4,
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