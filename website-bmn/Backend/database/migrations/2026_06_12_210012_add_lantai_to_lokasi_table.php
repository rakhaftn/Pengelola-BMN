<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lokasi', function (Blueprint $table) {
            $table->foreignId('lantai_id')->nullable()->after('id')->constrained('lantais')->nullOnDelete();
            $table->index('lantai_id');
        });
    }

    public function down(): void
    {
        Schema::table('lokasi', function (Blueprint $table) {
            $table->dropForeign(['lantai_id']);
            $table->dropColumn('lantai_id');
        });
    }
};
