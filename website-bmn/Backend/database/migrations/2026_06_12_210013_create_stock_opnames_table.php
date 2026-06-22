<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_opname', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi')->nullOnDelete();
            $table->date('tanggal_opname');
            $table->string('status', 20)->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('nomor_opname');
            $table->index('status');
            $table->index('tanggal_opname');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
