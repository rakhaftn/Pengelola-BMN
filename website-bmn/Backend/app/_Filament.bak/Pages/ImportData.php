<?php

namespace App\Filament\Pages;

use App\Imports\BarangImport;
use App\Imports\PegawaiImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ImportData extends Page
{
    protected static ?string $title = 'Import Data';
    protected static ?string $navigationLabel = 'Import Data';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 98;

    protected static string $view = 'filament.pages.import-data';

    public $barangFile = null;
    public $pegawaiFile = null;

    public function mount(): void
    {
        // Redirect non-admin/staff users
        if (!auth()->user()->hasRole('super_admin|staff')) {
            $this->redirect('/admin');
        }
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('barangFile')
                ->label('File Barang (Excel/CSV)')
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                ])
                ->maxSize(20480),

            FileUpload::make('pegawaiFile')
                ->label('File Pegawai (Excel/CSV)')
                ->acceptedFileTypes([
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-excel',
                    'text/csv',
                ])
                ->maxSize(20480),
        ];
    }

    public function importBarang(): void
    {
        if (!$this->barangFile) {
            Notification::make()
                ->title('Error')
                ->body('Pilih file terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        try {
            $import = new BarangImport();
            $results = $import->import($this->barangFile);

            Notification::make()
                ->title('Import Barang Selesai')
                ->body("{$results['created']} barang baru, {$results['updated']} diperbarui.")
                ->success()
                ->send();

            if (!empty($results['errors'])) {
                foreach (array_slice($results['errors'], 0, 5) as $error) {
                    Notification::make()
                        ->title('Peringatan')
                        ->body($error)
                        ->warning()
                        ->send();
                }
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function importPegawai(): void
    {
        if (!$this->pegawaiFile) {
            Notification::make()
                ->title('Error')
                ->body('Pilih file terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        try {
            $import = new PegawaiImport();
            $results = $import->import($this->pegawaiFile);

            Notification::make()
                ->title('Import Pegawai Selesai')
                ->body("{$results['created']} pegawai baru, {$results['updated']} diperbarui.")
                ->success()
                ->send();

            if (!empty($results['errors'])) {
                foreach (array_slice($results['errors'], 0, 5) as $error) {
                    Notification::make()
                        ->title('Peringatan')
                        ->body($error)
                        ->warning()
                        ->send();
                }
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
