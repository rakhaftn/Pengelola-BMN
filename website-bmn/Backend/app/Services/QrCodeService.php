<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\QrCode as QrCodeModel;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate & store QR code (SVG) for a barang.
     * Returns the storage path relative to the 'public' disk.
     */
    public function generateForBarang(Barang $barang): string
    {
        $dir = 'qrcodes';
        Storage::disk('public')->makeDirectory($dir);

        $filename = $dir . '/' . $barang->kode_barang . '.svg';

        // Encode the kode_barang; scanning resolves to the detail route.
        $svg = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate(route('barang.scan', ['kode' => $barang->kode_barang]));

        Storage::disk('public')->put($filename, $svg);

        QrCodeModel::updateOrCreate(
            ['barang_id' => $barang->id],
            ['kode' => $barang->kode_barang, 'path' => $filename]
        );

        return $filename;
    }

    public function inlineSvg(string $content, int $size = 200): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($content);
    }
}
