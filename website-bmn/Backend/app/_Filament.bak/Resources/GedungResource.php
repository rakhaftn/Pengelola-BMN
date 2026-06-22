<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GedungResource\Pages;
use App\Models\Gedung;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GedungResource extends Resource
{
    protected static ?string $model = Gedung::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Struktur Lokasi';
    protected static ?string $navigationLabel = 'Gedung';
    protected static ?string $modelLabel = 'Gedung';
    protected static ?string $pluralModelLabel = 'Gedung';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Gedung')->schema([
                Forms\Components\Select::make('direktorat_id')->label('Direktorat')
                    ->relationship('direktorat', 'nama')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('kode')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')->required(),
                Forms\Components\Textarea::make('alamat'),
                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('direktorat.nama')->label('Direktorat')->sortable(),
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('lantais_count')->counts('lantais')->label('Lantai')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direktorat_id')->label('Direktorat')
                    ->relationship('direktorat', 'nama'),
                Tables\Filters\SelectFilter::make('is_active')->options([1 => 'Aktif', 0 => 'Nonaktif']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('direktorat_id')->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGedungs::route('/'),
            'create' => Pages\CreateGedung::route('/create'),
            'edit' => Pages\EditGedung::route('/{record}/edit'),
        ];
    }
}
