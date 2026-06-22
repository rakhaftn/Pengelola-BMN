<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use Illuminate\Http\JsonResponse;

class UnitKerjaController extends Controller
{
    /**
     * Display a listing of unit kerja.
     */
    public function index(): JsonResponse
    {
        $data = UnitKerja::with('direktorat')
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'direktorat_id']);

        return response()->json([
            'success' => true,
            'message' => 'Unit kerja retrieved successfully',
            'data' => $data,
        ]);
    }

    /**
     * Display the specified unit kerja.
     */
    public function show(int $id): JsonResponse
    {
        $unit = UnitKerja::with('direktorat')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit kerja retrieved successfully',
            'data' => $unit,
        ]);
    }
}
