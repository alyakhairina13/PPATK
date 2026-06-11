<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_dokumen', function (Blueprint $table) {
            $table->id('id_dokumen');
            $table->foreignId('id_akta')->constrained('akta', 'id_akta');
            $table->string('nama_file', 255);
            $table->enum('format_extension', ['jpg', 'png', 'pdf']);
            $table->decimal('ukuran_berkas', 5, 2);
            $table->string('path_penyimpanan', 500);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_dokumen');
    }
};
