<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LantaiResource\Pages;
use App\Models\Lantai;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LantaiResource extends Resource
{
    protected static ?string $model = Lantai::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Struktur Lokasi';
    protected static ?string $navigationLabel = 'Lantai';
    protected static ?string $modelLabel = 'Lantai';
    protected static ?string $pluralModelLabel = 'Lantai';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Lantai')->schema([
                Forms\Components\Select::make('gedung_id')->label('Gedung')
                    ->relationship('gedung', 'nama')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('kode')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('nama')->required(),
                Forms\Components\TextInput::make('lantai_ke')->numeric()->label('Nomor Lantai'),
                Forms\Components\Textarea::make('keterangan'),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('gedung.direktorat.nama')->label('Direktorat')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('gedung.nama')->label('Gedung')->sortable(),
                Tables\Columns\TextColumn::make('kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('lantai_ke')->label('Lantai ke')->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')->label('Aktif'),
                Tables\Columns\TextColumn::make('lokasis_count')->counts('lokasis')->label('Lokasi')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gedung_id')->label('Gedung')
                    ->relationship('gedung', 'nama'),
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
            ->defaultSort('gedung_id')->defaultSort('lantai_ke');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLantais::route('/'),
            'create' => Pages\CreateLantai::route('/create'),
            'edit' => Pages\EditLantai::route('/{record}/edit'),
        ];
    }
}
