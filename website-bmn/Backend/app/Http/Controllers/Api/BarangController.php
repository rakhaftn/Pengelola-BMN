<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BarangCollection;
use App\Http\Resources\BarangResource;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarangController extends Controller
{
    /**
     * Display a listing of barang.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Barang::with(['kategori', 'lokasi', 'ruangan', 'gedung']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'ilike', "%{$search}%")
                    ->orWhere('kode_barang', 'ilike', "%{$search}%")
                    ->orWhere('merek', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        if ($request->filled('lokasi_id')) {
            $query->where('lokasi_id', $request->lokasi_id);
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100); // Limit 1-100

        $barang = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data barang retrieved successfully',
            'data' => BarangResource::collection($barang),
            'meta' => [
                'current_page' => $barang->currentPage(),
                'last_page' => $barang->lastPage(),
                'per_page' => $barang->perPage(),
                'total' => $barang->total(),
            ],
        ]);
    }

    /**
     * Store a newly created barang.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_barang,id'],
            'direktorat_id' => ['nullable', 'exists:direktorats,id'],
            'gedung_id' => ['nullable', 'exists:gedungs,id'],
            'lantai_id' => ['nullable', 'exists:lantais,id'],
            'lokasi_id' => ['nullable', 'exists:lokasi,id'],
            'ruangan_id' => ['nullable', 'exists:ruangan,id'],
            'merek' => ['nullable', 'string', 'max:100'],
            'nomor_seri' => ['nullable', 'string', 'max:100'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'nilai_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kondisi' => ['nullable', 'in:baik,rusak_ringan,rusak_berat'],
            'status' => ['nullable', 'in:pengadaan,tersedia,dipinjam,dalam_perawatan,rusak_ringan,rusak_berat,hilang,dihapuskan,dimusnahkan'],
            'keterangan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['kode_barang'] = Barang::generateKode();
        $validated['status'] = $validated['status'] ?? 'tersedia';
        $validated['kondisi'] = $validated['kondisi'] ?? 'baik';

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('barang', 'public');
        }

        $barang = Barang::create($validated);

        // Generate QR Code
        app(\App\Services\QrCodeService::class)->generateForBarang($barang);

        $barang->load(['kategori', 'lokasi', 'ruangan', 'gedung']);

        return response()->json([
            'success' => true,
            'message' => 'Barang created successfully',
            'data' => new BarangResource($barang),
        ], 201);
    }

    /**
     * Display the specified barang.
     */
    public function show(int $id): JsonResponse
    {
        $barang = Barang::with(['kategori', 'lokasi', 'ruangan', 'gedung', 'direktorat', 'qrCode'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Barang retrieved successfully',
            'data' => new BarangResource($barang),
        ]);
    }

    /**
     * Find barang by kode_barang (e.g., BMN-2026-000001).
     */
    public function findByKode(string $kode): JsonResponse
    {
        $barang = Barang::with(['kategori', 'lokasi', 'ruangan', 'gedung', 'direktorat', 'qrCode', 'histori'])
            ->where('kode_barang', $kode)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Barang found',
            'data' => new BarangResource($barang),
        ]);
    }

    /**
     * Update the specified barang.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['sometimes', 'string', 'max:255'],
            'kategori_id' => ['sometimes', 'exists:kategori_barang,id'],
            'direktorat_id' => ['nullable', 'exists:direktorats,id'],
            'gedung_id' => ['nullable', 'exists:gedungs,id'],
            'lantai_id' => ['nullable', 'exists:lantais,id'],
            'lokasi_id' => ['nullable', 'exists:lokasi,id'],
            'ruangan_id' => ['nullable', 'exists:ruangan,id'],
            'merek' => ['nullable', 'string', 'max:100'],
            'nomor_seri' => ['nullable', 'string', 'max:100'],
            'tahun_perolehan' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'nilai_perolehan' => ['nullable', 'numeric', 'min:0'],
            'kondisi' => ['nullable', 'in:baik,rusak_ringan,rusak_berat'],
            'status' => ['nullable', 'in:pengadaan,tersedia,dipinjam,dalam_perawatan,rusak_ringan,rusak_berat,hilang,dihapuskan,dimusnahkan'],
            'keterangan' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }
            $validated['foto'] = $request->file('foto')->store('barang', 'public');
        }

        $barang->update($validated);
        $barang->load(['kategori', 'lokasi', 'ruangan', 'gedung']);

        return response()->json([
            'success' => true,
            'message' => 'Barang updated successfully',
            'data' => new BarangResource($barang),
        ]);
    }

    /**
     * Remove the specified barang (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang deleted successfully',
        ]);
    }

    /**
     * Get barang histori.
     */
    public function histori(int $id): JsonResponse
    {
        $barang = Barang::findOrFail($id);
        $histori = $barang->histori()->with('user')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Histori retrieved successfully',
            'data' => $histori,
        ]);
    }
}
