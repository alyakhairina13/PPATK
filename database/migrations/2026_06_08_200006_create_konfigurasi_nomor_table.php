<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfigurasi_nomor', function (Blueprint $table) {
            $table->id();
            $table->string('pattern', 255)->default('{NOMOR}/{TAHUN}/{BULAN}-Rptm');
            $table->enum('reset_period', ['tahunan', 'bulanan'])->default('tahunan');
            $table->integer('starting_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_nomor');
    }
};
