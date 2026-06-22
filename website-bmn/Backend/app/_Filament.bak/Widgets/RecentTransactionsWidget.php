<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use App\Filament\Resources\PeminjamanResource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Peminjaman Terbaru';
    protected static ?int $sort = 3;
    protected string $tableHeight = '380px';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Peminjaman::query()
                    ->with('peminjam')
                    ->orderByDesc('created_at')
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('nomor_peminjaman')
                    ->label('Nomor')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('peminjam.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->description(fn (Peminjaman $r) => $r->unitKerja->nama ?? $r->peminjam->unitKerja->nama ?? '-'),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => Peminjaman::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'menunggu_persetujuan' => 'warning',
                        'disetujui' => 'info',
                        'ditolak' => 'danger',
                        'dipinjam' => 'primary',
                        'dikembalikan' => 'success',
                        'selesai' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('details_count')
                    ->label('Items')
                    ->badge()
                    ->counts('details'),
            ])
            ->actions([
                Tables\Actions\Action::make('buka')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Peminjaman $r): string => PeminjamanResource::getUrl('edit', ['record' => $r])),
            ])
            ->paginated(false);
    }
}
