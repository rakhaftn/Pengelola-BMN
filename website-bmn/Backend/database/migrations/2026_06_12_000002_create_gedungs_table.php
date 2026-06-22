<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gedungs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direktorat_id')->constrained('direktorats')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('kode');
            $table->index('direktorat_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gedungs');
    }
};
