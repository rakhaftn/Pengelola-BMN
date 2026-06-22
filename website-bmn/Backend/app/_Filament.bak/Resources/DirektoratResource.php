<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirektoratResource\Pages;
use App\Models\Direktorat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DirektoratResource extends Resource
{
    protected static ?string $model = Direktorat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Struktur Lokasi';
    protected static ?string $navigationLabel = 'Direktorat';
    protected static ?string $modelLabel = 'Direktorat';
    protected static ?string $pluralModelLabel = 'Direktorat';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Direktorat')->schema([
                Forms\Components\TextInput::make('kode')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')->required(),
                Forms\Components\TextInput::make('kepala'),
                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('kepala')->label('Kepala'),
                Tables\Columns\BooleanColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('gedungs_count')->counts('gedungs')->label('Gedung')->badge(),
            ])
            ->filters([
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
            ->defaultSort('nama');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirektorats::route('/'),
            'create' => Pages\CreateDirektorat::route('/create'),
            'edit' => Pages\EditDirektorat::route('/{record}/edit'),
        ];
    }
}
