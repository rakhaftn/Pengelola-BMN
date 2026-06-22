<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use App\Services\QrCodeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Barang';
    protected static ?string $modelLabel = 'Barang';
    protected static ?string $pluralModelLabel = 'Barang';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        // Hanya super_admin dan staff yang bisa create
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff'));
    }

    public static function canEdit($record): bool
    {
        // Hanya super_admin dan staff yang bisa edit
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff'));
    }

    public static function canDelete($record): bool
    {
        // Hanya super_admin dan staff yang bisa delete
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff'));
    }

    public static function canViewAny(): bool
    {
        // Semua user yang punya permission bisa view
        $user = auth()->user();
        return $user && ($user->can('view_any_barang') || $user->hasRole('user'));
    }

    public static function canView($record): bool
    {
        // Semua user yang punya permission bisa view
        $user = auth()->user();
        return $user && ($user->can('view_barang') || $user->hasRole('user'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Barang')->schema([
                Forms\Components\TextInput::make('kode_barang')
                    ->label('Kode Barang')
                    ->default(fn () => Barang::generateKode())
                    ->required()->unique(ignoreRecord: true)->readOnly()
                    ->helperText('Dibuat otomatis dengan format BMN-TAHUN-NOMOR'),
                Forms\Components\TextInput::make('nama')->required()->maxLength(255),
                Forms\Components\Select::make('kategori_id')->label('Kategori')
                    ->relationship('kategori', 'nama')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('merek')->maxLength(255),
                Forms\Components\TextInput::make('nomor_seri')->label('Nomor Seri')->maxLength(255),
                Forms\Components\TextInput::make('tahun_perolehan')->numeric()->minValue(1980)->maxValue(date('Y') + 1),
                Forms\Components\TextInput::make('nilai_perolehan')->numeric()->prefix('Rp'),
            ])->columns(2),

            Forms\Components\Section::make('Struktur Lokasi')->schema([
                Forms\Components\Select::make('direktorat_id')->label('Direktorat')
                    ->relationship('direktorat', 'nama')->searchable()->preload()
                    ->live(),
                Forms\Components\Select::make('gedung_id')->label('Gedung')
                    ->options(fn (Forms\Get $get) => \App\Models\Gedung::query()
                        ->when($get('direktorat_id'), fn ($q, $id) => $q->where('direktorat_id', $id))
                        ->pluck('nama', 'id'))
                    ->searchable()->preload()->live(),
                Forms\Components\Select::make('lantai_id')->label('Lantai')
                    ->options(fn (Forms\Get $get) => \App\Models\Lantai::query()
                        ->when($get('gedung_id'), fn ($q, $id) => $q->where('gedung_id', $id))
                        ->pluck('nama', 'id'))
                    ->searchable()->preload()->live(),
                Forms\Components\Select::make('lokasi_id')->label('Lokasi')
                    ->options(fn (Forms\Get $get) => \App\Models\Lokasi::query()
                        ->when($get('lantai_id'), fn ($q, $id) => $q->where('lantai_id', $id))
                        ->pluck('nama', 'id'))
                    ->searchable()->preload(),
                Forms\Components\Select::make('ruangan_id')->label('Ruangan')
                    ->options(fn (Forms\Get $get) => \App\Models\Ruangan::query()
                        ->when($get('lokasi_id'), fn ($q, $id) => $q->where('lokasi_id', $id))
                        ->pluck('nama', 'id'))
                    ->searchable()->preload(),
            ])->columns(3),

            Forms\Components\Section::make('Kondisi & Status')->schema([
                Forms\Components\Select::make('kondisi')->options(Barang::KONDISI)->default('baik')->required(),
                Forms\Components\Select::make('status')->options(Barang::STATUS)->default('tersedia')->required(),
                Forms\Components\Textarea::make('keterangan')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_barang')->label('Kode')->searchable()->sortable()
                    ->copyable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable()->weight('bold')
                    ->description(fn (Barang $r) => trim(($r->merek ?? '') . ' ' . ($r->nomor_seri ?? ''))),
                Tables\Columns\TextColumn::make('kategori.nama')->label('Kategori')->badge()->sortable(),
                Tables\Columns\TextColumn::make('direktorat.nama')->label('Dir')->toggleable()->sortable()
                    ->description(fn (Barang $r) => $r->gedung?->nama),
                Tables\Columns\TextColumn::make('ruangan.nama')->label('Ruangan')->toggleable()->sortable(),
                Tables\Columns\TextColumn::make('kondisi')->badge()
                    ->formatStateUsing(fn ($state) => Barang::KONDISI[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'baik' => 'success', 'rusak_ringan' => 'warning', 'rusak_berat' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Barang::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'pengadaan' => 'info',
                        'tersedia' => 'success',
                        'dipinjam' => 'primary',
                        'dalam_perawatan' => 'warning',
                        'rusak_ringan' => 'warning',
                        'rusak_berat' => 'danger',
                        'hilang' => 'danger',
                        'dihapuskan' => 'gray',
                        'dimusnahkan' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_id')->label('Kategori')->relationship('kategori', 'nama'),
                Tables\Filters\SelectFilter::make('status')->options(Barang::STATUS),
                Tables\Filters\SelectFilter::make('kondisi')->options(Barang::KONDISI),
                Tables\Filters\SelectFilter::make('direktorat_id')->label('Direktorat')->relationship('direktorat', 'nama'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('qr')
                        ->label('QR')->icon('heroicon-o-qr-code')->color('gray')
                        ->modalHeading(fn (Barang $r) => 'QR Code — ' . $r->kode_barang)
                        ->modalContent(fn (Barang $r) => view('filament.qr-modal', [
                            'barang' => $r,
                            'svg' => app(QrCodeService::class)->inlineSvg(route('barang.scan', $r->kode_barang), 240),
                        ]))
                        ->modalSubmitAction(false)->modalCancelActionLabel('Tutup'),
                    Tables\Actions\Action::make('scan')
                        ->label('Lihat Detail')->icon('heroicon-o-eye')->color('info')
                        ->url(fn (Barang $r) => route('barang.scan', $r->kode_barang))->openUrlInNewTab(),
                ])->label('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoriRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}