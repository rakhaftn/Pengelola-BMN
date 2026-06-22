<?php

namespace App\Http\Controllers;

use App\Imports\BarangImport;
use App\Imports\PegawaiImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function barang(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $file = $request->file('file');
            $import = new BarangImport();
            $results = $import->import($file->getRealPath());

            return response()->json([
                'success' => true,
                'message' => "Import selesai. {$results['created']} barang baru dibuat, {$results['updated']} diperbarui.",
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pegawai(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $file = $request->file('file');
            $import = new PegawaiImport();
            $results = $import->import($file->getRealPath());

            return response()->json([
                'success' => true,
                'message' => "Import selesai. {$results['created']} pegawai baru dibuat, {$results['updated']} diperbarui.",
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
