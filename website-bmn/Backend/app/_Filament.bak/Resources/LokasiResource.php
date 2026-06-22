<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LokasiResource\Pages;
use App\Models\Lokasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LokasiResource extends Resource
{
    protected static ?string $model = Lokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Struktur Lokasi';
    protected static ?string $navigationLabel = 'Lokasi';
    protected static ?string $modelLabel = 'Lokasi';
    protected static ?string $pluralModelLabel = 'Lokasi';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Lokasi')->schema([
                Forms\Components\Select::make('lantai_id')->label('Lantai')
                    ->relationship('lantai', 'nama')->searchable()->preload()
                    ->live(),
                Forms\Components\TextInput::make('kode')->required()->unique(ignoreRecord: true)->maxLength(50)->placeholder('LOK-001'),
                Forms\Components\TextInput::make('nama')->required()->maxLength(255),
                Forms\Components\Textarea::make('alamat')->columnSpanFull(),
                Forms\Components\Textarea::make('keterangan')->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lantai.gedung.direktorat.nama')->label('Dir')->sortable()->toggleable()->limit(15),
                Tables\Columns\TextColumn::make('lantai.gedung.nama')->label('Gedung')->sortable()->toggleable()->limit(15),
                Tables\Columns\TextColumn::make('lantai.nama')->label('Lantai')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable()->badge(),
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('alamat')->limit(30)->toggleable(),
                Tables\Columns\TextColumn::make('ruangan_count')->counts('ruangan')->label('Ruangan')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lantai_id')->label('Lantai')
                    ->relationship('lantai', 'nama'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('lantai_id')->defaultSort('kode');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLokasis::route('/'),
            'create' => Pages\CreateLokasi::route('/create'),
            'edit' => Pages\EditLokasi::route('/{record}/edit'),
        ];
    }
}
