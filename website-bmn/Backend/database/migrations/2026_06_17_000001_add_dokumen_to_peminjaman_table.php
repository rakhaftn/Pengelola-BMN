<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('dokumen_atasan')->nullable()->after('approved_atasan_at');
            $table->string('dokumen_petugas')->nullable()->after('approved_petugas_at');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['dokumen_atasan', 'dokumen_petugas']);
        });
    }
};
