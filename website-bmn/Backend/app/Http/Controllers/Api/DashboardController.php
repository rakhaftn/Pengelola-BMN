<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            // Barang stats
            'total_barang' => Barang::count(),
            'barang_tersedia' => Barang::where('status', 'tersedia')->count(),
            'barang_dipinjam' => Barang::where('status', 'dipinjam')->count(),
            'barang_rusak' => Barang::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count(),

            // Peminjaman stats
            'total_peminjaman' => Peminjaman::count(),
            'peminjaman_menunggu' => Peminjaman::where('status', 'menunggu_persetujuan')->count(),
            'peminjaman_disetujui' => Peminjaman::where('status', 'disetujui')->count(),
            'peminjaman_dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
            'peminjaman_selesai' => Peminjaman::where('status', 'selesai')->count(),

            // Overdue
            'peminjaman_terlambat' => Peminjaman::where('status', 'dipinjam')
                ->whereDate('tanggal_kembali_rencana', '<', now()->toDateString())
                ->count(),
        ];

        // Chart data
        $barangByKondisi = Barang::select('kondisi', DB::raw('count(*) as total'))
            ->groupBy('kondisi')
            ->pluck('total', 'kondisi');

        $barangByStatus = Barang::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $peminjamanByStatus = Peminjaman::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Monthly peminjaman (last 6 months)
        $monthlyPeminjaman = Peminjaman::select(
            DB::raw("EXTRACT(YEAR FROM created_at) as year"),
            DB::raw("EXTRACT(MONTH FROM created_at) as month"),
            DB::raw('count(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'bulan' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                'total' => $item->total,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard stats retrieved successfully',
            'data' => [
                'stats' => $stats,
                'barang_by_kondisi' => $barangByKondisi,
                'barang_by_status' => $barangByStatus,
                'peminjaman_by_status' => $peminjamanByStatus,
                'monthly_peminjaman' => $monthlyPeminjaman,
            ],
        ]);
    }

    /**
     * Get recent activity (peminjaman & pengembalian).
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 10);
        $limit = min(max($limit, 1), 50);

        $recentPeminjaman = Peminjaman::with('peminjam')
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'type' => 'peminjaman',
                'id' => $p->id,
                'nomor' => $p->nomor_peminjaman,
                'peminjam' => $p->peminjam?->name,
                'status' => $p->status,
                'status_label' => Peminjaman::STATUS[$p->status] ?? $p->status,
                'tanggal' => $p->tanggal_pinjam,
                'tanggal_kembali' => $p->tanggal_kembali_rencana,
                'updated_at' => $p->updated_at,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Recent activity retrieved successfully',
            'data' => $recentPeminjaman,
        ]);
    }

    /**
     * Get audit logs (for admin monitoring).
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $query = AuditLog::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('event', 'ilike', "%{$search}%")
                    ->orWhere('auditable_type', 'ilike', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('model_type')) {
            $query->where('auditable_type', 'like', '%' . $request->model_type);
        }

        $perPage = $request->integer('per_page', 20);
        $perPage = min(max($perPage, 1), 100);

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully',
            'data' => $logs,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
