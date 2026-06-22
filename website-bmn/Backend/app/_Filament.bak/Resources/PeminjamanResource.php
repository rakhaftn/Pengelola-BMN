<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeminjamanResource\Pages;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = null;
    protected static ?string $navigationLabel = 'Peminjaman';
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff') || $user->hasRole('user'));
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        // User biasa hanya bisa edit draft
        if ($user->hasRole('user')) {
            return $record->status === 'draft';
        }
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff'));
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('staff'));
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->can('view_any_peminjaman') || $user->hasRole('user'));
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        return $user && ($user->can('view_peminjaman') || $user->hasRole('user'));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Peminjaman')->schema([
                Forms\Components\TextInput::make('nomor_peminjaman')
                    ->default(fn () => Peminjaman::generateNomor())
                    ->required()->unique(ignoreRecord: true)->readOnly(),
                Forms\Components\Select::make('peminjam_id')->label('Peminjam')
                    ->relationship('peminjam', 'name')->searchable()->preload()->required()
                    ->default(fn () => Auth::id())
                    ->disabled(fn () => auth()->user()->hasRole('user')),
                Forms\Components\Select::make('unit_kerja_id')->label('Unit Kerja')
                    ->relationship('unitKerja', 'nama')->searchable()->preload(),
                Forms\Components\Select::make('status')->options(Peminjaman::STATUS)
                    ->default('draft')->required()->disabled()->dehydrated()
                    ->helperText('Status berubah otomatis melalui alur persetujuan.'),
                Forms\Components\DatePicker::make('tanggal_pinjam')->required()->default(now()),
                Forms\Components\DatePicker::make('tanggal_kembali_rencana')->label('Rencana Kembali')->required()
                    ->default(now()->addDays(7)),
                Forms\Components\Textarea::make('keperluan')->columnSpanFull()->required(),
            ])->columns(2),

            Forms\Components\Section::make('Barang yang Dipinjam')->schema([
                Forms\Components\Repeater::make('details')
                    ->relationship()
                    ->label('Daftar Barang')
                    ->schema([
                        Forms\Components\Select::make('barang_id')->label('Barang')
                            ->options(fn () => Barang::where('status', 'tersedia')->pluck('nama', 'id'))
                            ->searchable()->required()
                            ->getOptionLabelUsing(fn ($value) => optional(Barang::find($value))->nama),
                        Forms\Components\Select::make('kondisi_pinjam')->label('Kondisi Saat Pinjam')
                            ->options(Barang::KONDISI)->default('baik')->required(),
                        Forms\Components\TextInput::make('catatan'),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->addActionLabel('Tambah Barang')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_peminjaman')->label('Nomor')->searchable()->sortable()
                    ->copyable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('peminjam.name')->label('Peminjam')->searchable()->sortable()
                    ->description(fn (Peminjaman $r) => $r->unitKerja->nama ?? null),
                Tables\Columns\TextColumn::make('details_count')->counts('details')->label('Jml Barang')->badge(),
                Tables\Columns\TextColumn::make('tanggal_pinjam')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_kembali_rencana')->label('Rencana Kembali')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => Peminjaman::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray', 'menunggu_persetujuan' => 'warning',
                        'disetujui' => 'info', 'ditolak' => 'danger',
                        'dipinjam' => 'primary', 'dikembalikan' => 'success', 'selesai' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('dokumen_atasan')
                    ->label('Dok. Atasan')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->tooltip(fn ($state) => $state ? 'Sudah diunggah' : 'Belum diunggah'),
                Tables\Columns\IconColumn::make('dokumen_petugas')
                    ->label('Dok. Petugas')
                    ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->tooltip(fn ($state) => $state ? 'Sudah diunggah' : 'Belum diunggah'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(Peminjaman::STATUS),
                Tables\Filters\SelectFilter::make('peminjam_id')->label('Peminjam')->relationship('peminjam', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Ajukan (draft -> menunggu_persetujuan)
                Tables\Actions\Action::make('ajukan')
                    ->label('Ajukan')->icon('heroicon-o-paper-airplane')->color('warning')
                    ->visible(fn (Peminjaman $r) => $r->status === 'draft' && ($r->peminjam_id === auth()->id() || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('staff')))
                    ->requiresConfirmation()
                    ->action(function (Peminjaman $r) {
                        app(PeminjamanService::class)->ajukan($r);
                        Notification::make()->title('Peminjaman diajukan untuk persetujuan')->success()->send();
                    }),

                // Setujui Super Admin
                Tables\Actions\Action::make('setujui_admin')
                    ->label('Setujui (Super Admin)')->icon('heroicon-o-check-circle')->color('info')
                    ->visible(fn (Peminjaman $r) => $r->status === 'menunggu_persetujuan' && auth()->user()->hasRole('super_admin') && ! $r->approved_atasan_id)
                    ->form([
                        Forms\Components\FileUpload::make('dokumen_atasan')
                            ->label('Upload Dokumen Persetujuan')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->directory('dokumen-persetujuan')
                            ->required(),
                    ])
                    ->action(function (Peminjaman $r, array $data) {
                        $r->update(['dokumen_atasan' => $data['dokumen_atasan']]);
                        app(PeminjamanService::class)->setujuiAtasan($r, Auth::id());
                        Notification::make()->title('Disetujui oleh Super Admin')->success()->send();
                    }),

                // Setujui Staff (final -> disetujui)
                Tables\Actions\Action::make('setujui_staff')
                    ->label('Konfirmasi (Staff BMN)')->icon('heroicon-o-shield-check')->color('success')
                    ->visible(fn (Peminjaman $r) => $r->status === 'menunggu_persetujuan' && $r->approved_atasan_id && auth()->user()->hasRole('staff') && ! $r->approved_petugas_id)
                    ->form([
                        Forms\Components\FileUpload::make('dokumen_petugas')
                            ->label('Upload Dokumen Konfirmasi')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120)
                            ->directory('dokumen-persetujuan')
                            ->required(),
                    ])
                    ->action(function (Peminjaman $r, array $data) {
                        $r->update(['dokumen_petugas' => $data['dokumen_petugas']]);
                        app(PeminjamanService::class)->setujuiPetugas($r, Auth::id());
                        Notification::make()->title('Dikonfirmasi oleh Staff BMN')->success()->send();
                    }),

                // Tolak
                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (Peminjaman $r) => $r->status === 'menunggu_persetujuan' && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('staff')))
                    ->form([Forms\Components\Textarea::make('alasan')->label('Alasan Penolakan')->required()])
                    ->action(function (Peminjaman $r, array $data) {
                        app(PeminjamanService::class)->tolak($r, Auth::id(), $data['alasan']);
                        Notification::make()->title('Peminjaman ditolak')->danger()->send();
                    }),

                // Serah Terima (disetujui -> dipinjam) - Staff only
                Tables\Actions\Action::make('serah_terima')
                    ->label('Serah Terima Barang')->icon('heroicon-o-hand-raised')->color('primary')
                    ->visible(fn (Peminjaman $r) => $r->status === 'disetujui' && auth()->user()->hasRole('staff'))
                    ->requiresConfirmation()
                    ->modalDescription('Barang akan ditandai sebagai dipinjam dan tercatat di histori.')
                    ->action(function (Peminjaman $r) {
                        app(PeminjamanService::class)->serahTerima($r);
                        Notification::make()->title('Barang diserahterimakan')->success()->send();
                    }),

                // Kembalikan (dipinjam -> selesai) - Staff only
                Tables\Actions\Action::make('kembalikan')
                    ->label('Proses Pengembalian')->icon('heroicon-o-arrow-uturn-left')->color('success')
                    ->visible(fn (Peminjaman $r) => $r->status === 'dipinjam' && auth()->user()->hasRole('staff'))
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_pengembalian')->required()->default(now()),
                        Forms\Components\Select::make('kondisi_barang')->options(Barang::KONDISI)->default('baik')->required(),
                        Forms\Components\Textarea::make('catatan'),
                    ])
                    ->action(function (Peminjaman $r, array $data) {
                        app(PeminjamanService::class)->kembalikan($r, $data);
                        Notification::make()->title('Pengembalian berhasil dicatat')->success()->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('pdf_form')
                        ->label('Form Peminjaman (PDF)')->icon('heroicon-o-document')
                        ->url(fn (Peminjaman $r) => route('dokumen.form-peminjaman', $r))->openUrlInNewTab(),
                    Tables\Actions\Action::make('pdf_surat_pernyataan')
                        ->label('Surat Pernyataan')->icon('heroicon-o-document')
                        ->url(fn (Peminjaman $r) => route('dokumen.surat-pernyataan', $r))->openUrlInNewTab(),
                    Tables\Actions\Action::make('pdf_bast')
                        ->label('Berita Acara Serah Terima')->icon('heroicon-o-document')
                        ->visible(fn (Peminjaman $r) => in_array($r->status, ['dipinjam', 'selesai']))
                        ->url(fn (Peminjaman $r) => route('dokumen.bast', $r))->openUrlInNewTab(),
                    Tables\Actions\Action::make('view_dok_atasan')
                        ->label('Lihat Dok. Atasan')->icon('heroicon-o-document-arrow-down')
                        ->visible(fn (Peminjaman $r) => $r->dokumen_atasan)
                        ->url(fn (Peminjaman $r) => asset('storage/' . $r->dokumen_atasan))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('view_dok_petugas')
                        ->label('Lihat Dok. Petugas')->icon('heroicon-o-document-arrow-down')
                        ->visible(fn (Peminjaman $r) => $r->dokumen_petugas)
                        ->url(fn (Peminjaman $r) => asset('storage/' . $r->dokumen_petugas))
                        ->openUrlInNewTab(),
                    Tables\Actions\EditAction::make()
                        ->visible(fn (Peminjaman $r) => (in_array($r->status, ['draft', 'ditolak'])) && (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('staff') || auth()->user()->hasRole('user'))),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (Peminjaman $r) => auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('staff')),
                ])->label('Lainnya')->icon('heroicon-o-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);

        // User biasa hanya bisa melihat peminjaman mereka sendiri
        if ($user->hasRole('user') && !$user->hasRole('super_admin') && !$user->hasRole('staff')) {
            $query->where('peminjam_id', $user->id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPeminjamen::route('/'),
            'create' => Pages\CreatePeminjaman::route('/create'),
            'edit' => Pages\EditPeminjaman::route('/{record}/edit'),
        ];
    }
}
