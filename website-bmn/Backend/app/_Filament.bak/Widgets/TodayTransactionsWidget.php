<?php

namespace App\Filament\Widgets;

use App\Models\Peminjaman;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Transaksi Hari Ini';
    protected static ?int $sort = 2;
    protected string $tableHeight = '300px';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Peminjaman::query()
                    ->whereDate('created_at', now()->toDateString())
                    ->with(['peminjam', 'details.barang'])
                    ->orderByDesc('created_at')
            )
            ->columns([
                TextColumn::make('nomor_peminjaman')
                    ->label('Nomor')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('peminjam.name')
                    ->label('Peminjam')
                    ->searchable(),

                TextColumn::make('details_count')
                    ->label('Barang')
                    ->badge()
                    ->counts('details'),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y'),

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

                TextColumn::make('approvedPetugas.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('-'),
            ])
            ->paginated(false);
    }
}
