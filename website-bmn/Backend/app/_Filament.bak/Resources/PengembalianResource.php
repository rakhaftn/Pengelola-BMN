<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengembalianResource\Pages;
use App\Models\Barang;
use App\Models\Pengembalian;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pengembalian';
    protected static ?string $modelLabel = 'Pengembalian';
    protected static ?string $pluralModelLabel = 'Pengembalian';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_pengembalian')->label('Nomor')->searchable()->sortable()
                    ->badge()->color('primary')->copyable(),
                Tables\Columns\TextColumn::make('peminjaman.nomor_peminjaman')->label('No. Peminjaman')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('peminjaman.peminjam.name')->label('Peminjam')->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pengembalian')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('kondisi_barang')->badge()
                    ->formatStateUsing(fn ($state) => Barang::KONDISI[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'baik' => 'success', 'rusak_ringan' => 'warning', 'rusak_berat' => 'danger', default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('ada_kerusakan')->label('Kerusakan')->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')->falseColor('success'),
                Tables\Columns\TextColumn::make('diterimaOleh.name')->label('Diterima Oleh')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kondisi_barang')->options(Barang::KONDISI),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('pdf_ba')
                        ->label('Berita Acara (PDF)')->icon('heroicon-o-document')
                        ->url(fn (Pengembalian $r) => route('dokumen.ba-pengembalian', $r))->openUrlInNewTab(),
                    Tables\Actions\Action::make('pdf_surat_pengembalian')
                        ->label('Surat Pengembalian (PDF)')->icon('heroicon-o-document')
                        ->url(fn (Pengembalian $r) => route('dokumen.surat-pengembalian', $r))->openUrlInNewTab(),
                ])->label('Dokumen')->icon('heroicon-o-document-arrow-up'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false; // dibuat lewat alur peminjaman
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengembalians::route('/'),
        ];
    }
}
