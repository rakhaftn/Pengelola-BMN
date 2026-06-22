<?php

namespace App\Filament\Resources\BarangResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriRelationManager extends RelationManager
{
    protected static string $relationship = 'histori';
    protected static ?string $title = 'Histori Barang';
    protected static ?string $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('judul')
            ->columns([
                Tables\Columns\TextColumn::make('terjadi_pada')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('tipe')->badge()
                    ->color(fn ($state) => match ($state) {
                        'perolehan' => 'success', 'peminjaman' => 'info', 'pengembalian' => 'primary',
                        'perubahan_kondisi' => 'warning', 'perbaikan' => 'warning',
                        'penghapusan' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('judul')->wrap(),
                Tables\Columns\TextColumn::make('deskripsi')->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('user.name')->label('Oleh')->toggleable(),
            ])
            ->defaultSort('terjadi_pada', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
