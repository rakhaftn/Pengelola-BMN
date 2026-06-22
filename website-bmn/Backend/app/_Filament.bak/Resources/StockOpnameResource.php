<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockOpnameResource\Pages;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Stock Opname';
    protected static ?string $modelLabel = 'Stock Opname';
    protected static ?string $pluralModelLabel = 'Stock Opname';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Stock Opname')->schema([
                Forms\Components\TextInput::make('nomor_opname')
                    ->default(fn () => StockOpname::generateNomor())
                    ->required()->readOnly(),
                Forms\Components\Select::make('lokasi_id')->label('Lokasi')
                    ->relationship('lokasi', 'nama')->searchable()->preload(),
                Forms\Components\DatePicker::make('tanggal_opname')->required()->default(now()),
                Forms\Components\Select::make('status')->options(StockOpname::STATUS)->default('draft')->required(),
                Forms\Components\Textarea::make('catatan')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_opname')->label('Nomor')->searchable()->sortable()
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('user.name')->label('Petugas')->sortable(),
                Tables\Columns\TextColumn::make('lokasi.nama')->label('Lokasi')->sortable(),
                Tables\Columns\TextColumn::make('tanggal_opname')->date('d M Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => StockOpname::STATUS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray', 'berlangsung' => 'warning', 'selesai' => 'success', 'dicancel' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('details_count')->counts('details')->label('Items')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(StockOpname::STATUS),
                Tables\Filters\SelectFilter::make('lokasi_id')->label('Lokasi')->relationship('lokasi', 'nama'),
            ])
            ->actions([
                Tables\Actions\Action::make('scan')
                    ->label('Scan QR')->icon('heroicon-o-qr-code')->color('info')
                    ->url(fn (StockOpname $r): string => route('stock-opname.scan', $r)),
                Tables\Actions\Action::make('mulai')
                    ->label('Mulai')->icon('heroicon-o-play')->color('warning')
                    ->visible(fn (StockOpname $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (StockOpname $r) {
                        $r->update(['status' => 'berlangsung']);
                        Notification::make()->title('Stock opname dimulai')->success()->send();
                    }),
                Tables\Actions\Action::make('selesai')
                    ->label('Selesai')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (StockOpname $r) => $r->status === 'berlangsung')
                    ->requiresConfirmation()
                    ->action(function (StockOpname $r) {
                        $r->update(['status' => 'selesai', 'tanggal_selesai' => now()]);
                        Notification::make()->title('Stock opname selesai')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }
}
