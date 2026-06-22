<?php

namespace App\Filament\SuperAdmin;

use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\BarangResource;
use App\Filament\Resources\DirektoratResource;
use App\Filament\Resources\GedungResource;
use App\Filament\Resources\KategoriBarangResource;
use App\Filament\Resources\LantaiResource;
use App\Filament\Resources\LokasiResource;
use App\Filament\Resources\PeminjamanResource;
use App\Filament\Resources\PengembalianResource;
use App\Filament\Resources\RuanganResource;
use App\Filament\Resources\StockOpnameResource;
use App\Filament\Resources\UnitKerjaResource;
use App\Filament\Resources\UserResource;
use App\Filament\SuperAdmin\Pages\Dashboard;
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

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('BMN - Super Admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->resources([
                // Master Data
                BarangResource::class,
                KategoriBarangResource::class,
                // User Management
                UserResource::class,
                // Lokasi
                DirektoratResource::class,
                GedungResource::class,
                LantaiResource::class,
                LokasiResource::class,
                RuanganResource::class,
                UnitKerjaResource::class,
                // Transaksi
                PeminjamanResource::class,
                PengembalianResource::class,
                StockOpnameResource::class,
                // Audit
                AuditLogResource::class,
            ])
            ->navigationGroups([
                'Dashboard',
                'Master Data',
                'Struktur Lokasi',
                'Peminjaman',
                'Transaksi',
                'Audit',
                'Manajemen User',
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
            ])
            ->databaseNotifications();
    }
}
