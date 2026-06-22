<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Peminjaman $peminjaman, public int $hari)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Peminjaman Terlambat!')
            ->error()
            ->line("Peminjaman {$this->peminjaman->nomor_peminjaman} sudah terlambat {$this->hari} hari!")
            ->line('Tanggal rencana kembali: ' . $this->peminjaman->tanggal_kembali_rencana->format('d M Y'))
            ->action('Proses Sekarang', url("/admin/peminjaman/{$this->peminjaman->id}/edit"))
            ->line('Harap segera tindak lanjuti pengembalian barang.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'overdue',
            'peminjaman_id' => $this->peminjaman->id,
            'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
            'hari' => $this->hari,
            'message' => "⚠️ Peminjaman {$this->peminjaman->nomor_peminjaman} terlambat {$this->hari} hari!",
        ];
    }
}
