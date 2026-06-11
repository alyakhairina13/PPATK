<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klien', function (Blueprint $table) {
            $table->id('id_klien');
            $table->string('nama_lengkap', 150);
            $table->char('nik', 16)->unique();
            $table->string('tempat_tanggal_lahir', 100);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->text('alamat');
            $table->string('nomor_telepon', 20);
            $table->string('pekerjaan', 100);
            $table->string('npwp', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klien');
    }
};
