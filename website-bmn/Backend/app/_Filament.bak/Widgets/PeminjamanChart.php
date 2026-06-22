<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class PeminjamanChart extends LineChartWidget
{
    protected static ?string $heading = 'Tren Peminjaman';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = null;

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        // Ambil data peminjaman per bulan untuk tahun yang dipilih
        $monthlyData = Peminjaman::whereYear('tanggal_pinjam', $year)
            ->selectRaw("EXTRACT(MONTH FROM tanggal_pinjam)::int as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Generate label dan data untuk 12 bulan
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlyData[$i] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Jumlah Peminjaman',
                    'data' => $data,
                    'fill' => true,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
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
