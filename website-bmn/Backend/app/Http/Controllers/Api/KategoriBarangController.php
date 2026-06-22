<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriBarang;
use Illuminate\Http\JsonResponse;

class KategoriBarangController extends Controller
{
    /**
     * Display a listing of kategori barang.
     */
    public function index(): JsonResponse
    {
        $data = KategoriBarang::orderBy('nama')->get(['id', 'nama', 'kode', 'keterangan']);

        return response()->json([
            'success' => true,
            'message' => 'Kategori barang retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Display the specified kategori barang.
     */
    public function show(int $id): JsonResponse
    {
        $kategori = KategoriBarang::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Kategori barang retrieved successfully',
            'data' => $kategori,
        ]);
    }
}
