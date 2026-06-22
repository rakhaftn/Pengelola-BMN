<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JatuhTempoNotification extends Notification implements ShouldQueue
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
            ->subject('Pengingat Jatuh Tempo Peminjaman')
            ->line("Peminjaman {$this->peminjaman->nomor_peminjaman} akan jatuh tempo dalam {$this->hari} hari.")
            ->line('Tanggal rencana kembali: ' . $this->peminjaman->tanggal_kembali_rencana->format('d M Y'))
            ->action('Lihat Detail', url("/admin/peminjaman/{$this->peminjaman->id}/edit"))
            ->line('Harap pastikan barang dikembalikan tepat waktu.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'jatuh_tempo',
            'peminjaman_id' => $this->peminjaman->id,
            'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
            'hari' => $this->hari,
            'message' => "Peminjaman {$this->peminjaman->nomor_peminjaman} jatuh tempo dalam {$this->hari} hari",
        ];
    }
}
