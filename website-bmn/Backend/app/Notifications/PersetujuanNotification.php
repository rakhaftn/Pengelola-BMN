<?php

namespace App\Notifications;

use App\Models\Peminjaman;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PersetujuanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Peminjaman $peminjaman,
        public string $status, // 'disetujui' atau 'ditolak'
        public ?string $alasan = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->status === 'disetujui' ? 'Peminjaman Disetujui' : 'Peminjaman Ditolak')
            ->line("Peminjaman {$this->peminjaman->nomor_peminjaman} telah {$this->status}.");

        if ($this->status === 'disetujui') {
            $message->line('Silakan menuju ruang penyimpanan untuk serah terima barang.');
        } else {
            $message->line("Alasan: {$this->alasan}");
        }

        return $message
            ->action('Lihat Detail', url("/admin/peminjaman/{$this->peminjaman->id}/edit"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'persetujuan',
            'status' => $this->status,
            'peminjaman_id' => $this->peminjaman->id,
            'nomor_peminjaman' => $this->peminjaman->nomor_peminjaman,
            'message' => "Peminjaman {$this->peminjaman->nomor_peminjaman} {$this->status}",
        ];
    }
}
