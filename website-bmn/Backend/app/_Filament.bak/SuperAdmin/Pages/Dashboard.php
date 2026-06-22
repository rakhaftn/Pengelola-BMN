<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

class Dashboard extends Page
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.super-admin.pages.dashboard';

    protected static ?string $title = 'Dashboard Super Admin';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public ?string $pending = '0';
    public ?string $approved = '0';
    public ?string $onLoan = '0';
    public ?string $returned = '0';
    public ?string $rejected = '0';

    public function mount(): void
    {
        $this->refreshStats();
    }

    public function refreshStats(): void
    {
        $this->pending = Peminjaman::where('status', 'menunggu_persetujuan')->count();
        $this->approved = Peminjaman::where('status', 'disetujui')->count();
        $this->onLoan = Peminjaman::where('status', 'dipinjam')->count();
        $this->returned = Peminjaman::where('status', 'selesai')->count();
        $this->rejected = Peminjaman::where('status', 'ditolak')->count();
    }

    protected function getViewData(): array
    {
        return [
            'pendingRequests' => Peminjaman::with(['peminjam'])
                ->where('status', 'menunggu_persetujuan')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'recentActivity' => Peminjaman::with(['peminjam'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(),
            'totalBarang' => Barang::count(),
            'totalPeminjaman' => Peminjaman::count(),
            'totalUsers' => User::count(),
        ];
    }
}
