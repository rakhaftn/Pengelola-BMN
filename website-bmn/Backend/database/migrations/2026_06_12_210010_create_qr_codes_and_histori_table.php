<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->string('kode')->unique(); // sama dgn kode_barang
            $table->string('path')->nullable(); // path file svg/png
            $table->timestamps();
        });

        // Histori barang - jejak perjalanan barang
        Schema::create('barang_histori', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // tipe: perolehan, perpindahan, peminjaman, pengembalian, perubahan_kondisi, perbaikan, penghapusan
            $table->string('tipe');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('kondisi_sebelum')->nullable();
            $table->string('kondisi_sesudah')->nullable();
            $table->string('status_sebelum')->nullable();
            $table->string('status_sesudah')->nullable();
            $table->timestamp('terjadi_pada')->nullable();
            $table->timestamps();

            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_histori');
        Schema::dropIfExists('qr_codes');
    }
};
