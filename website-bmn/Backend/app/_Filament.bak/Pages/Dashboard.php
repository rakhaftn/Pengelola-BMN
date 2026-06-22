<?php

namespace App\Filament\Pages;

use Illuminate\Http\RedirectResponse;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $title = 'Dashboard';
    protected static ?string $slug = '';

    public static function getRoutePath(): string
    {
        return '/';
    }

    public function mount(): void
    {
        // Hapus redirect karena masing-masing role punya panel sendiri
    }
}
