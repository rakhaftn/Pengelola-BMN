<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use App\Notifications\PengajuanBaruNotification;
use App\Notifications\PersetujuanNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PeminjamanService
{
    public function __construct(private BarangHistoriService $histori) {}

    /**
     * Ajukan peminjaman: draft -> menunggu_persetujuan
     */
    public function ajukan(Peminjaman $peminjaman): void
    {
        $peminjaman->update(['status' => 'menunggu_persetujuan']);

        // Kirim notifikasi ke staff BMN
        $staff = User::role('staff')->get();
        Notification::send($staff, new PengajuanBaruNotification($peminjaman));
    }

    /**
     * Persetujuan Super Admin (ijin peminjaman).
     */
    public function setujuiAtasan(Peminjaman $peminjaman, int $userId): void
    {
        $peminjaman->update([
            'approved_atasan_id' => $userId,
            'approved_atasan_at' => now(),
        ]);
    }

    /**
     * Konfirmasi Staff BMN (konfirmasi stock, bukti surat) -> disetujui (final approval).
     */
    public function setujuiPetugas(Peminjaman $peminjaman, int $userId): void
    {
        $peminjaman->update([
            'approved_petugas_id' => $userId,
            'approved_petugas_at' => now(),
            'status'              => 'disetujui',
        ]);

        // Kirim notifikasi ke peminjam
        if ($peminjaman->peminjam) {
            $peminjaman->peminjam->notify(new PersetujuanNotification($peminjaman, 'disetujui'));
        }
    }

    public function tolak(Peminjaman $peminjaman, int $userId, string $alasan): void
    {
        $peminjaman->update([
            'status'           => 'ditolak',
            'rejected_by'      => $userId,
            'rejected_at'      => now(),
            'alasan_penolakan' => $alasan,
        ]);

        // Kirim notifikasi ke peminjam
        if ($peminjaman->peminjam) {
            $peminjaman->peminjam->notify(new PersetujuanNotification($peminjaman, 'ditolak', $alasan));
        }
    }

    /**
     * Serah terima barang: disetujui -> dipinjam.
     * Set barang status menjadi 'dipinjam' & catat histori.
     */
    public function serahTerima(Peminjaman $peminjaman): void
    {
        DB::transaction(function () use ($peminjaman) {
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->details as $detail) {
                $barang = $detail->barang;
                if (! $barang) {
                    continue;
                }
                $statusLama = $barang->status;
                $barang->update(['status' => 'dipinjam']);

                $this->histori->catat($barang, 'peminjaman', "Dipinjam via {$peminjaman->nomor_peminjaman}", [
                    'deskripsi'      => 'Barang diserahterimakan kepada ' . optional($peminjaman->peminjam)->name,
                    'status_sebelum' => $statusLama,
                    'status_sesudah' => 'dipinjam',
                ]);
            }
        });
    }

    /**
     * Proses pengembalian: dipinjam -> dikembalikan -> selesai.
     * Mengembalikan status barang ke tersedia (atau perbaikan bila rusak).
     */
    public function kembalikan(Peminjaman $peminjaman, array $data): Pengembalian
    {
        return DB::transaction(function () use ($peminjaman, $data) {
            $pengembalian = Pengembalian::create([
                'nomor_pengembalian'   => Pengembalian::generateNomor(),
                'peminjaman_id'        => $peminjaman->id,
                'diterima_oleh'        => Auth::id(),
                'tanggal_pengembalian' => $data['tanggal_pengembalian'] ?? now(),
                'kondisi_barang'       => $data['kondisi_barang'] ?? 'baik',
                'ada_kerusakan'        => ($data['kondisi_barang'] ?? 'baik') !== 'baik',
                'catatan'              => $data['catatan'] ?? null,
            ]);

            foreach ($peminjaman->details as $detail) {
                $barang = $detail->barang;
                if (! $barang) {
                    continue;
                }
                $kondisiKembali = $data['kondisi_barang'] ?? 'baik';
                $detail->update(['kondisi_kembali' => $kondisiKembali]);

                $statusLama  = $barang->status;
                $kondisiLama = $barang->kondisi;
                $statusBaru  = $kondisiKembali === 'baik' ? 'tersedia' : 'dalam_perawatan';

                $barang->update([
                    'status'  => $statusBaru,
                    'kondisi' => $kondisiKembali,
                ]);

                $this->histori->catat($barang, 'pengembalian', "Dikembalikan via {$pengembalian->nomor_pengembalian}", [
                    'deskripsi'       => 'Barang dikembalikan oleh ' . optional($peminjaman->peminjam)->name,
                    'kondisi_sebelum' => $kondisiLama,
                    'kondisi_sesudah' => $kondisiKembali,
                    'status_sebelum'  => $statusLama,
                    'status_sesudah'  => $statusBaru,
                ]);
            }

            $peminjaman->update([
                'status'                 => 'selesai',
                'tanggal_kembali_aktual' => $data['tanggal_pengembalian'] ?? now(),
            ]);

            return $pengembalian;
        });
    }
}
