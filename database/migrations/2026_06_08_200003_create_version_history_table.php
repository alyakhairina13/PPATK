<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('version_history', function (Blueprint $table) {
            $table->id('id_version');
            $table->foreignId('id_akta')->constrained('akta', 'id_akta');
            $table->string('versi_ke', 10);
            $table->longText('backup_konten_teks');
            $table->dateTime('timestamp_perubahan');
            $table->string('diubah_oleh', 150);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_history');
    }
};
