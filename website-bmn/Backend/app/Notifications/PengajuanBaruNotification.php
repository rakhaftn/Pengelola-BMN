<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PengajuanBaruNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Peminjaman $peminjaman)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Peminjaman Baru')
            ->line("Pengajuan {$this->peminjaman->nomor_peminjaman} dari {$this->peminjaman->peminjam->name}")
            ->line('Terdapat pengajuan peminjaman baru yang memerlukan persetujuan.')
            ->action('Lihat Detail', url("/admin/peminjaman/{$this->peminjaman->id}/edit"))
            ->line('Terima kasih!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pengajuan_baru',
            'peminjaman_id' => $this->peminjaman->id,
            'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
            'peminjam' => $this->peminjaman->peminjam->name,
            'message' => "Pengajuan {$this->peminjaman->nomor_peminjaman} dari {$this->peminjaman->peminjam->name}",
        ];
    }
}
