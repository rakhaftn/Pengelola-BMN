<?php

namespace App\Console\Commands;

use App\Models\Peminjaman;
use App\Notifications\JatuhTempoNotification;
use App\Notifications\OverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPeminjamanNotifications extends Command
{
    protected $signature = 'peminjaman:send-notifications';
    protected $description = 'Kirim notifikasi jatuh tempo dan overdue peminjaman';

    public function handle(): int
    {
        $this->info('Memulai pengiriman notifikasi...');

        // 1. Jatuh Tempo - 3 hari sebelum
        $jatuhTempo = Peminjaman::where('status', 'dipinjam')
            ->whereDate('tanggal_kembali_rencana', now()->addDays(3)->toDateString())
            ->with('peminjam')
            ->get();

        foreach ($jatuhTempo as $peminjaman) {
            if ($peminjaman->peminjam) {
                $peminjaman->peminjam->notify(new JatuhTempoNotification($peminjaman, 3));
                $this->line("Notifikasi jatuh tempo untuk {$peminjaman->nomor_peminjaman}");
            }
        }

        // 2. Overdue - sudah lewat tanggal
        $overdue = Peminjaman::where('status', 'dipinjam')
            ->whereDate('tanggal_kembali_rencana', '<', now()->toDateString())
            ->with('peminjam')
            ->get();

        foreach ($overdue as $peminjaman) {
            $hari = now()->diffInDays($peminjaman->tanggal_kembali_rencana);
            if ($peminjaman->peminjam) {
                $peminjaman->peminjam->notify(new OverdueNotification($peminjaman, $hari));
                $this->line("Notifikasi overdue untuk {$peminjaman->nomor_peminjaman} ({$hari} hari)");
            }
        }

        $this->info("Selesai. Jatuh tempo: {$jatuhTempo->count()}, Overdue: {$overdue->count()}");
        return Command::SUCCESS;
    }
}
