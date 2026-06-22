<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use App\Filament\Resources\PeminjamanResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class OverdueAlertWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Peminjaman Terlambat';
    protected static ?int $sort = 4;
    protected string $tableHeight = '250px';

    protected function getTableQuery(): Builder
    {
        return Peminjaman::query()
            ->where('status', 'dipinjam')
            ->where('tanggal_kembali_rencana', '<', now()->toDateString())
            ->with(['peminjam', 'unitKerja'])
            ->orderBy('tanggal_kembali_rencana', 'asc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('nomor_peminjaman')
                    ->label('Nomor')
                    ->badge()
                    ->color('danger')
                    ->searchable(),

                Tables\Columns\TextColumn::make('peminjam.name')
                    ->label('Peminjam')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_kembali_rencana')
                    ->label('Rencana Kembali')
                    ->date('d M Y')
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('hari_lewat')
                    ->label('Hari Lewat')
                    ->getStateUsing(function (Peminjaman $record): string {
                        $days = now()->diffInDays($record->tanggal_kembali_rencana);
                        return $days . ' hari';
                    })
                    ->badge()
                    ->color('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-m-arrow-right')
                    ->url(fn (Peminjaman $r): string => PeminjamanResource::getUrl('edit', ['record' => $r])),
            ])
            ->paginated(false);
    }

    public static function isLazy(): bool
    {
        return false;
    }
}