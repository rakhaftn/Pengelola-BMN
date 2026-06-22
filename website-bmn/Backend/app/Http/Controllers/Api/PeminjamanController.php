<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PeminjamanCollection;
use App\Http\Resources\PeminjamanResource;
use App\Http\Resources\DetailPeminjamanResource;
use App\Models\Barang;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function __construct(private PeminjamanService $service) {}

    /**
     * Display a listing of peminjaman.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Peminjaman::with(['peminjam', 'unitKerja', 'details.barang.kategori']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by peminjam (for user context)
        if ($request->filled('peminjam_id')) {
            $query->where('peminjam_id', $request->peminjam_id);
        }

        // Filter by date range
        if ($request->filled('tanggal_pinjam_dari')) {
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_pinjam_dari);
        }
        if ($request->filled('tanggal_pinjam_sampai')) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_pinjam_sampai);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_peminjaman', 'ilike', "%{$search}%")
                    ->orWhere('tujuan', 'ilike', "%{$search}%")
                    ->orWhereHas('peminjam', function ($q) use ($search) {
                        $q->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $perPage = $request->integer('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $peminjaman = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data peminjaman retrieved successfully',
            'data' => PeminjamanResource::collection($peminjaman),
            'meta' => [
                'current_page' => $peminjaman->currentPage(),
                'last_page' => $peminjaman->lastPage(),
                'per_page' => $peminjaman->perPage(),
                'total' => $peminjaman->total(),
            ],
        ]);
    }

    /**
     * Store a newly created peminjaman.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'peminjam_id' => ['required', 'exists:users,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerja,id'],
            'tanggal_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_kembali_rencana' => ['required', 'date', 'after:tanggal_pinjam'],
            'tujuan' => ['required', 'string', 'max:500'],
            'keperluan' => ['nullable', 'string', 'max:500'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.barang_id' => ['required', 'exists:barang,id'],
            'details.*.jumlah' => ['required', 'integer', 'min:1'],
        ]);

        return DB::transaction(function () use ($validated) {
            $peminjaman = Peminjaman::create([
                'nomor_peminjaman' => Peminjaman::generateNomor(),
                'peminjam_id' => $validated['peminjam_id'],
                'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $validated['tanggal_kembali_rencana'],
                'tujuan' => $validated['tujuan'],
                'keperluan' => $validated['keperluan'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($validated['details'] as $detail) {
                DetailPeminjaman::create([
                    'peminjaman_id' => $peminjaman->id,
                    'barang_id' => $detail['barang_id'],
                    'jumlah' => $detail['jumlah'],
                ]);
            }

            $peminjaman->load(['peminjam', 'unitKerja', 'details.barang.kategori']);

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman created successfully',
                'data' => new PeminjamanResource($peminjaman),
            ], 201);
        });
    }

    /**
     * Display the specified peminjaman.
     */
    public function show(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::with([
            'peminjam.unitKerja',
            'unitKerja',
            'approvedAtasan',
            'approvedPetugas',
            'details.barang.kategori',
            'pengembalian',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman retrieved successfully',
            'data' => new PeminjamanResource($peminjaman),
        ]);
    }

    /**
     * Update peminjaman (only for draft status).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman dengan status draft yang dapat diedit',
            ], 422);
        }

        $validated = $request->validate([
            'tanggal_pinjam' => ['sometimes', 'date', 'after_or_equal:today'],
            'tanggal_kembali_rencana' => ['sometimes', 'date'],
            'tujuan' => ['sometimes', 'string', 'max:500'],
            'keperluan' => ['nullable', 'string', 'max:500'],
        ]);

        $peminjaman->update($validated);
        $peminjaman->load(['peminjam', 'unitKerja', 'details.barang.kategori']);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman updated successfully',
            'data' => new PeminjamanResource($peminjaman),
        ]);
    }

    /**
     * Submit peminjaman for approval (draft -> menunggu_persetujuan).
     */
    public function submit(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman dengan status draft yang dapat diajukan',
            ], 422);
        }

        $this->service->ajukan($peminjaman);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diajukan untuk persetujuan',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Approve by atasan/SuperAdmin (menunggu_persetujuan -> pending staff approval).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return response()->json([
                'success' => false,
                'message' => 'Status peminjaman tidak valid untuk persetujuan',
            ], 422);
        }

        $validated = $request->validate([
            'dokumen_atasan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $data = ['user_id' => auth()->id()];

        if ($request->hasFile('dokumen_atasan')) {
            $data['dokumen_atasan'] = $request->file('dokumen_atasan')->store('dokumen', 'public');
        }

        $this->service->setujuiAtasan($peminjaman, auth()->id());

        if ($request->hasFile('dokumen_atasan')) {
            $peminjaman->update(['dokumen_atasan' => $data['dokumen_atasan']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman disetujui oleh atasan',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Approve by staff BMN (final approval -> disetujui).
     */
    public function approveStaff(Request $request, int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'menunggu_persetujuan') {
            return response()->json([
                'success' => false,
                'message' => 'Status peminjaman tidak valid untuk konfirmasi staff',
            ], 422);
        }

        if (!$peminjaman->approved_atasan_id) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman belum disetujui oleh atasan',
            ], 422);
        }

        $validated = $request->validate([
            'dokumen_petugas' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('dokumen_petugas')) {
            $peminjaman->update(['dokumen_petugas' => $request->file('dokumen_petugas')->store('dokumen', 'public')]);
        }

        $this->service->setujuiPetugas($peminjaman, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman dikonfirmasi oleh staff BMN',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Reject peminjaman.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if (!in_array($peminjaman->status, ['menunggu_persetujuan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status peminjaman tidak valid untuk penolakan',
            ], 422);
        }

        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ]);

        $this->service->tolak($peminjaman, auth()->id(), $validated['alasan_penolakan']);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman ditolak',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Serah terima barang (disetujui -> dipinjam).
     */
    public function serahTerima(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Status peminjaman harus disetujui untuk serah terima',
            ], 422);
        }

        $this->service->serahTerima($peminjaman);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diserahterimakan',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Cancel peminjaman (draft only).
     */
    public function cancel(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman dengan status draft yang dapat dibatalkan',
            ], 422);
        }

        $peminjaman->update(['status' => 'ditolak', 'alasan_penolakan' => 'Dibatalkan oleh peminjam']);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dibatalkan',
            'data' => new PeminjamanResource($peminjaman->fresh()),
        ]);
    }

    /**
     * Get details (barang) of a peminjaman.
     */
    public function details(int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $details = $peminjaman->details()->with('barang.kategori')->get();

        return response()->json([
            'success' => true,
            'message' => 'Details retrieved successfully',
            'data' => DetailPeminjamanResource::collection($details),
        ]);
    }

    /**
     * Add detail (barang) to peminjaman.
     */
    public function addDetail(Request $request, int $id): JsonResponse
    {
        $peminjaman = Peminjaman::findOrFail($id);

        if ($peminjaman->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman dengan status draft yang dapat diubah',
            ], 422);
        }

        $validated = $request->validate([
            'barang_id' => ['required', 'exists:barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ]);

        // Check if barang already added
        $exists = DetailPeminjaman::where('peminjaman_id', $id)
            ->where('barang_id', $validated['barang_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Barang sudah ada dalam peminjaman ini',
            ], 422);
        }

        // Check barang availability
        $barang = Barang::find($validated['barang_id']);
        if ($barang->status !== 'tersedia') {
            return response()->json([
                'success' => false,
                'message' => "Barang {$barang->kode_barang} tidak tersedia (status: {$barang->status})",
            ], 422);
        }

        $detail = DetailPeminjaman::create([
            'peminjaman_id' => $id,
            'barang_id' => $validated['barang_id'],
            'jumlah' => $validated['jumlah'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => new DetailPeminjamanResource($detail->load('barang.kategori')),
        ], 201);
    }

    /**
     * Remove detail from peminjaman.
     */
    public function removeDetail(int $peminjaman, int $detail): JsonResponse
    {
        $p = Peminjaman::findOrFail($peminjaman);

        if ($p->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya peminjaman dengan status draft yang dapat diubah',
            ], 422);
        }

        $detailModel = DetailPeminjaman::where('peminjaman_id', $peminjaman)
            ->where('id', $detail)
            ->firstOrFail();

        $detailModel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus dari peminjaman',
        ]);
    }
}
