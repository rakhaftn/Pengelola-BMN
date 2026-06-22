<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockOpnameScanController extends Controller
{
    public function show(StockOpname $stockOpname)
    {
        $details = $stockOpname->details()->with('barang')->get();
        return view('stock-opname.scan', compact('stockOpname', 'details'));
    }

    public function scan(Request $request, StockOpname $stockOpname)
    {
        $request->validate([
            'kode_barang' => 'required|string',
            'status' => 'required|in:ditemukan,tidak_ditemukan,rusak',
            'kondisi' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $barang = Barang::where('kode_barang', $request->kode_barang)->first();

        if (!$barang) {
            return back()->with('error', 'Barang dengan kode ' . $request->kode_barang . ' tidak ditemukan.');
        }

        // Cek apakah sudah di-scan
        $existing = StockOpnameDetail::where('opname_id', $stockOpname->id)
            ->where('barang_id', $barang->id)
            ->first();

        if ($existing) {
            return back()->with('warning', 'Barang ' . $barang->kode_barang . ' sudah pernah discan.');
        }

        // Simpan hasil scan
        StockOpnameDetail::create([
            'opname_id' => $stockOpname->id,
            'barang_id' => $barang->id,
            'status' => $request->status,
            'kondisi_sebelum' => $barang->kondisi,
            'kondisi_sesudah' => $request->kondisi ?? $barang->kondisi,
            'catatan' => $request->catatan,
            'scanned_at' => now(),
        ]);

        return back()->with('success', 'Barang ' . $barang->kode_barang . ' berhasil discan!');
    }
}
