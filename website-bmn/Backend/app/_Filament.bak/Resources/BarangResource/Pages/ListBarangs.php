<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('export.barang')),
            Actions\Action::make('importPage')
                ->label('Import Data')
                ->icon('heroicon-o-document-arrow-up')
                ->color('warning')
                ->url(fn () => url('/admin/import-data')),
        ];
    }
}
