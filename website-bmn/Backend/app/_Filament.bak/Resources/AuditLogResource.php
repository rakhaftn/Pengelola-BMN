<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administrasi';
    protected static ?string $navigationLabel = 'Audit Log';
    protected static ?string $modelLabel = 'Audit Log';
    protected static ?string $pluralModelLabel = 'Audit Log';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Pengguna')->searchable()->default('Sistem'),
                Tables\Columns\TextColumn::make('event')->badge()
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success', 'updated' => 'warning',
                        'deleted', 'force_deleted' => 'danger',
                        'login' => 'info', 'logout' => 'gray', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('description')->label('Deskripsi')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->options([
                    'created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted',
                    'login' => 'Login', 'logout' => 'Logout',
                ]),
                Tables\Filters\SelectFilter::make('user_id')->label('Pengguna')->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail Aktivitas')->schema([
                Infolists\Components\TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s'),
                Infolists\Components\TextEntry::make('user.name')->label('Pengguna')->default('Sistem'),
                Infolists\Components\TextEntry::make('event')->badge(),
                Infolists\Components\TextEntry::make('description')->label('Deskripsi'),
                Infolists\Components\TextEntry::make('auditable_type')->label('Tipe Objek'),
                Infolists\Components\TextEntry::make('auditable_id')->label('ID Objek'),
                Infolists\Components\TextEntry::make('ip_address')->label('IP Address'),
                Infolists\Components\TextEntry::make('user_agent')->label('User Agent')->columnSpanFull(),
            ])->columns(2),
            Infolists\Components\Section::make('Perubahan Data')->schema([
                Infolists\Components\KeyValueEntry::make('old_values')->label('Data Lama'),
                Infolists\Components\KeyValueEntry::make('new_values')->label('Data Baru'),
            ])->columns(2)->collapsible(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
