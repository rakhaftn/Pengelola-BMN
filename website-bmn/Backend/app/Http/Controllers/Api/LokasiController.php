<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Direktorat;
use App\Models\Gedung;
use App\Models\Lantai;
use App\Models\Lokasi;
use App\Models\Ruangan;
use App\Models\UnitKerja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    /**
     * Get all direktorat.
     */
    public function direktorat(): JsonResponse
    {
        $data = Directorate::orderBy('nama')->get(['id', 'nama', 'kode']);

        return response()->json([
            'success' => true,
            'message' => 'Direktorat retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get all gedung.
     */
    public function gedung(Request $request): JsonResponse
    {
        $query = Gedung::query();

        if ($request->filled('direktorat_id')) {
            $query->where('direktorat_id', $request->direktorat_id);
        }

        $data = $query->orderBy('nama')->get(['id', 'nama', 'kode', 'direktorat_id']);

        return response()->json([
            'success' => true,
            'message' => 'Gedung retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get all lantai.
     */
    public function lantai(Request $request): JsonResponse
    {
        $query = Lantai::query();

        if ($request->filled('gedung_id')) {
            $query->where('gedung_id', $request->gedung_id);
        }

        $data = $query->orderBy('nama')->get(['id', 'nama', 'gedung_id']);

        return response()->json([
            'success' => true,
            'message' => 'Lantai retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get all ruangan.
     */
    public function ruangan(Request $request): JsonResponse
    {
        $query = Ruangan::query();

        if ($request->filled('gedung_id')) {
            $query->where('gedung_id', $request->gedung_id);
        }

        if ($request->filled('lantai_id')) {
            $query->where('lantai_id', $request->lantai_id);
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $data = $query->orderBy('nama')->get(['id', 'nama', 'gedung_id', 'lantai_id', 'lokasi_id']);

        return response()->json([
            'success' => true,
            'message' => 'Ruangan retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get all unit kerja.
     */
    public function unitKerja(): JsonResponse
    {
        $data = UnitKerja::orderBy('nama')->get(['id', 'nama', 'kode']);

        return response()->json([
            'success' => true,
            'message' => 'Unit kerja retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Get lokasi (raw).
     */
    public function lokasi(Request $request): JsonResponse
    {
        $query = Lokasi::query();

        if ($request->filled('gedung_id')) {
            $query->where('gedung_id', $request->gedung_id);
        }

        if ($request->filled('lantai_id')) {
            $query->where('lantai_id', $request->lantai_id);
        }

        $data = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lokasi retrieved successfully',
            'data' => $data,
        ]);
    }
}
