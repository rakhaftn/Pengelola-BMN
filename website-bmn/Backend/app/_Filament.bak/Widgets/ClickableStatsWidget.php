<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class ClickableStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    #[On('open-stat-modal')]
    public function openModal(string $filter): void
    {
        $this->dispatch('show-modal', filter: $filter);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Barang', Barang::query()->count())
                ->description('Aset terdaftar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/barangs'",
                    'class' => 'cursor-pointer hover:bg-primary-50 transition-colors',
                ]),

            Stat::make('Barang Tersedia', Barang::query()->where('status', 'tersedia')->count())
                ->description('Siap dipinjam')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/barangs?tableFilters[status][value]=tersedia'",
                    'class' => 'cursor-pointer hover:bg-success-50 transition-colors',
                ]),

            Stat::make('Sedang Dipinjam', Barang::query()->where('status', 'dipinjam')->count())
                ->description('Sedang digunakan')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('info')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/barangs?tableFilters[status][value]=dipinjam'",
                    'class' => 'cursor-pointer hover:bg-info-50 transition-colors',
                ]),

            Stat::make('Menunggu Persetujuan', Peminjaman::query()->where('status', 'menunggu_persetujuan')->count())
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/peminjamen?tableFilters[status][value]=menunggu_persetujuan'",
                    'class' => 'cursor-pointer hover:bg-warning-50 transition-colors',
                ]),

            Stat::make('Rusak Ringan', Barang::query()->where('kondisi', 'rusak_ringan')->count())
                ->description('Perlu perbaikan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/barangs?tableFilters[kondisi][value]=rusak_ringan'",
                    'class' => 'cursor-pointer hover:bg-warning-50 transition-colors',
                ]),

            Stat::make('Rusak Berat', Barang::query()->where('kondisi', 'rusak_berat')->count())
                ->description('Perlu perhatian khusus')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/barangs?tableFilters[kondisi][value]=rusak_berat'",
                    'class' => 'cursor-pointer hover:bg-danger-50 transition-colors',
                ]),

            Stat::make('Peminjaman Terlambat', $this->getOverdueCount())
                ->description('Melewati tanggal rencana')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/peminjamen?tableFilters[status][value]=dipinjam'",
                    'class' => 'cursor-pointer hover:bg-danger-50 transition-colors',
                ]),

            Stat::make('Pengembalian Bulan Ini', $this->getPengembalianBulanIni())
                ->description('Tercatat bulan ini')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('success')
                ->extraAttributes([
                    'onclick' => "window.location.href = '/admin/pengembalians'",
                    'class' => 'cursor-pointer hover:bg-success-50 transition-colors',
                ]),
        ];
    }

    protected function getOverdueCount(): int
    {
        return Peminjaman::query()
            ->where('status', 'dipinjam')
            ->where('tanggal_kembali_rencana', '<', now()->toDateString())
            ->count();
    }

    protected function getPengembalianBulanIni(): int
    {
        return Pengembalian::query()
            ->whereMonth('tanggal_pengembalian', now()->month)
            ->whereYear('tanggal_pengembalian', now()->year)
            ->count();
    }
}
