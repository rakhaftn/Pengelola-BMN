<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique(); // BMN-2026-000001
            $table->string('nama');
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_barang')->nullOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi')->nullOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->nullOnDelete();
            $table->string('merek')->nullable();
            $table->string('nomor_seri')->nullable();
            $table->integer('tahun_perolehan')->nullable();
            $table->decimal('nilai_perolehan', 15, 2)->nullable();
            // Kondisi: baik, rusak_ringan, rusak_berat
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            // Status: tersedia, dipinjam, perbaikan, hilang, dihapuskan
            $table->enum('status', ['tersedia', 'dipinjam', 'perbaikan', 'hilang', 'dihapuskan'])->default('tersedia');
            $table->text('keterangan')->nullable();
            $table->string('foto')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
