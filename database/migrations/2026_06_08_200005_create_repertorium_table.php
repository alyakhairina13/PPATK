<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repertorium', function (Blueprint $table) {
            $table->id('id_repertorium');
            $table->foreignId('id_akta')->unique()->constrained('akta', 'id_akta');
            $table->string('nomor_akta_resmi', 100)->unique();
            $table->integer('indeks_urutan');
            $table->char('bulan_buku', 2);
            $table->char('tahun_buku', 4);
            $table->dateTime('timestamp_generasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repertorium');
    }
};
