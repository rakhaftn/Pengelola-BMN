<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Services\BarangHistoriService;
use App\Services\QrCodeService;
use Filament\Resources\Pages\CreateRecord;

class CreateBarang extends CreateRecord
{
    protected static string $resource = BarangResource::class;

    protected function afterCreate(): void
    {
        app(QrCodeService::class)->generateForBarang($this->record);

        app(BarangHistoriService::class)->catat(
            $this->record,
            'perolehan',
            'Barang dicatat ke sistem',
            [
                'deskripsi'      => 'Barang baru ditambahkan dengan kondisi ' . ($this->record->kondisi),
                'status_sesudah' => $this->record->status,
                'kondisi_sesudah' => $this->record->kondisi,
            ]
        );
    }
}
