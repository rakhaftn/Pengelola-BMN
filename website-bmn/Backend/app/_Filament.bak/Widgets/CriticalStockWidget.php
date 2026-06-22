<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Filament\Resources\BarangResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CriticalStockWidget extends BaseWidget
{
    protected static ?string $heading = '🚨 Barang Kondisi Kritis';
    protected static ?int $sort = 5;
    protected string $tableHeight = '250px';

    protected function getTableQuery(): Builder
    {
        return Barang::query()
            ->whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])
            ->with(['kategori', 'lokasi'])
            ->orderByRaw("CASE WHEN kondisi = 'rusak_berat' THEN 1 WHEN kondisi = 'rusak_ringan' THEN 2 ELSE 3 END")
            ->orderBy('nama');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Barang')
                    ->searchable()
                    ->description(fn (Barang $r) => $r->kategori->nama ?? '-'),

                Tables\Columns\TextColumn::make('kondisi')
                    ->label('Kondisi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Barang::KONDISI[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'rusak_ringan' => 'warning',
                        'rusak_berat' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('lokasi.nama')
                    ->label('Lokasi')
                    ->description(fn (Barang $r) => $r->ruangan->nama ?? '-'),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Barang $r): string => BarangResource::getUrl('edit', ['record' => $r])),
            ])
            ->paginated(false);
    }

    public static function isLazy(): bool
    {
        return false;
    }
}