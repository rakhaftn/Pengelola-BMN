<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengembalianResource;
use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    public function __construct(private PeminjamanService $service) {}

    /**
     * Display a listing of pengembalian.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pengembalian::with(['peminjaman.peminjam', 'peminjaman.details.barang']);

        // Search by nomor
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengembalian', 'ilike', "%{$search}%")
                    ->orWhereHas('peminjaman', function ($q) use ($search) {
                        $q->where('nomor_peminjaman', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filter by date
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pengembalian', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pengembalian', '<=', $request->tanggal_sampai);
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $pengembalian = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data pengembalian retrieved successfully',
            'data' => PengembalianResource::collection($pengembalian),
            'meta' => [
                'current_page' => $pengembalian->currentPage(),
                'last_page' => $pengembalian->lastPage(),
                'per_page' => $pengembalian->perPage(),
                'total' => $pengembalian->total(),
            ],
        ]);
    }

    /**
     * Store a new pengembalian.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'peminjaman_id' => ['required', 'exists:peminjaman,id'],
            'tanggal_pengembalian' => ['nullable', 'date'],
            'kondisi_barang' => ['nullable', 'in:baik,rusak_ringan,rusak_berat'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $peminjaman = Peminjaman::findOrFail($validated['peminjaman_id']);

        if ($peminjaman->status !== 'dipinjam') {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman belum pada tahap siap dikembalikan',
            ], 422);
        }

        $pengembalian = $this->service->kembalikan($peminjaman, [
            'tanggal_pengembalian' => $validated['tanggal_pengembalian'] ?? now()->toDateString(),
            'kondisi_barang' => $validated['kondisi_barang'] ?? 'baik',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian berhasil dicatat',
            'data' => new PengembalianResource($pengembalian->load(['peminjaman.peminjam', 'peminjaman.details.barang'])),
        ], 201);
    }

    /**
     * Display the specified pengembalian.
     */
    public function show(int $id): JsonResponse
    {
        $pengembalian = Pengembalian::with([
            'peminjaman.peminjam',
            'peminjaman.details.barang.kategori',
            'peminjaman.details.barang.lokasi',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian retrieved successfully',
            'data' => new PengembalianResource($pengembalian),
        ]);
    }

    /**
     * Get pengembalian by peminjaman.
     */
    public function byPeminjaman(int $peminjaman): JsonResponse
    {
        $p = Peminjaman::findOrFail($peminjaman);
        $pengembalian = $p->pengembalian;

        if (!$pengembalian) {
            return response()->json([
                'success' => false,
                'message' => 'Pengembalian tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengembalian retrieved successfully',
            'data' => new PengembalianResource($pengembalian->load(['peminjaman.peminjam', 'peminjaman.details.barang'])),
        ]);
    }
}
