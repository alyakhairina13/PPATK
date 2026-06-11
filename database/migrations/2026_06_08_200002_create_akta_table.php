<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akta', function (Blueprint $table) {
            $table->id('id_akta');
            $table->foreignId('id_klien')->constrained('klien', 'id_klien');
            $table->foreignId('id_user')->constrained('users', 'id_user');
            $table->enum('jenis_template', ['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat']);
            $table->longText('konten_teks_utama');
            $table->enum('status_workflow', ['Draft', 'Diverifikasi', 'Final', 'Selesai'])->default('Draft');
            $table->dateTime('tanggal_dibuat');
            $table->dateTime('last_updated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akta');
    }
};
