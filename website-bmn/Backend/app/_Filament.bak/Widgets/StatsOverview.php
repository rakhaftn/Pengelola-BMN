<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            // Statistik Barang
            Stat::make('Total Barang', Barang::count())
                ->description('Aset terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Barang Tersedia', Barang::where('status', 'tersedia')->count())
                ->description('Siap dipinjam')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Sedang Dipinjam', Barang::where('status', 'dipinjam')->count())
                ->description('Sedang digunakan')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('info'),

            // Statistik Peminjaman
            Stat::make('Menunggu Persetujuan', Peminjaman::where('status', 'menunggu_persetujuan')->count())
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Statistik Kondisi Barang
            Stat::make('Rusak Ringan', Barang::where('kondisi', 'rusak_ringan')->count())
                ->description('Perlu perbaikan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Rusak Berat', Barang::where('kondisi', 'rusak_berat')->count())
                ->description('Perlu perhatian khusus')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            // Statistik Overdue
            Stat::make('Peminjaman Terlambat', $this->getOverdueCount())
                ->description('Melewati tanggal rencana')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger'),

            // Statistik Pengembalian
            Stat::make('Pengembalian Bulan Ini', $this->getPengembalianBulanIni())
                ->description('Tercatat bulan ini')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('success'),
        ];
    }

    /**
     * Hitung peminjaman yang overdue (lewat tanggal rencana kembali, belum dikembalikan)
     */
    protected function getOverdueCount(): int
    {
        return Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_kembali_rencana', '<', now()->toDateString())
            ->count();
    }

    /**
     * Hitung pengembalian yang tercatat bulan ini
     */
    protected function getPengembalianBulanIni(): int
    {
        return Pengembalian::whereRaw("EXTRACT(MONTH FROM tanggal_pengembalian::date) = ? AND EXTRACT(YEAR FROM tanggal_pengembalian::date) = ?", [now()->month, now()->year])
            ->count();
    }
}