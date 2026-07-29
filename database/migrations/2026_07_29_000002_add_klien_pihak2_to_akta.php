<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akta', function (Blueprint $table) {
            $table->foreignId('id_klien_pihak2')
                ->nullable()
                ->after('id_klien')
                ->constrained('klien', 'id_klien')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('akta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_klien_pihak2');
        });
    }
};
