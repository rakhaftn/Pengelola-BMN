<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_peminjaman')->unique(); // PJM-2026-000001
            $table->foreignId('peminjam_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            // Status workflow: draft, menunggu_persetujuan, disetujui, ditolak, dipinjam, dikembalikan, selesai
            $table->enum('status', [
                'draft',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'dipinjam',
                'dikembalikan',
                'selesai',
            ])->default('draft');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->date('tanggal_kembali_aktual')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('keperluan')->nullable();
            // Persetujuan atasan
            $table->foreignId('approved_atasan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_atasan_at')->nullable();
            // Persetujuan petugas BMN
            $table->foreignId('approved_petugas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_petugas_at')->nullable();
            // Penolakan
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->text('catatan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
