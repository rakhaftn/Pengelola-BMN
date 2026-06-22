<?php

namespace App\Providers\Filament;

use App\Filament\Resources\BarangResource;
use App\Filament\Resources\KategoriBarangResource;
use App\Filament\Resources\DirektoratResource;
use App\Filament\Resources\GedungResource;
use App\Filament\Resources\LantaiResource;
use App\Filament\Resources\LokasiResource;
use App\Filament\Resources\RuanganResource;
use App\Filament\Resources\PeminjamanResource;
use App\Filament\Resources\PengembalianResource;
use App\Filament\Resources\StockOpnameResource;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('TRASET - Admin BMN')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \Filament\Widgets\AccountWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\TodayTransactionsWidget::class,
                \App\Filament\Widgets\RecentTransactionsWidget::class,
                \App\Filament\Widgets\OverdueAlertWidget::class,
                \App\Filament\Widgets\CriticalStockWidget::class,
            ])
            ->navigationGroups([
                'Dashboard',
                'Master Data',
                'Struktur Lokasi',
                'Transaksi',
                'Audit',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
