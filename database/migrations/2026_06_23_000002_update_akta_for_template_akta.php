<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akta', function (Blueprint $table) {
            $table->foreignId('template_id')
                ->nullable()
                ->after('id_user')
                ->constrained('template_akta', 'id_template_akta')
                ->nullOnDelete();
            $table->string('jenis_template_baru', 150)->nullable()->after('template_id');
        });

        DB::table('akta')->update([
            'jenis_template_baru' => DB::raw('jenis_template'),
        ]);

        Schema::table('akta', function (Blueprint $table) {
            $table->dropColumn('jenis_template');
        });

        Schema::table('akta', function (Blueprint $table) {
            $table->renameColumn('jenis_template_baru', 'jenis_template');
        });
    }

    public function down(): void
    {
        Schema::table('akta', function (Blueprint $table) {
            $table->enum('jenis_template_lama', ['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat'])->nullable()->after('id_user');
        });

        DB::table('akta')->update([
            'jenis_template_lama' => DB::raw("CASE
                WHEN jenis_template IN ('AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat') THEN jenis_template
                ELSE 'AJB'
            END"),
        ]);

        Schema::table('akta', function (Blueprint $table) {
            $table->dropColumn('jenis_template');
        });

        Schema::table('akta', function (Blueprint $table) {
            $table->renameColumn('jenis_template_lama', 'jenis_template');
            $table->dropConstrainedForeignId('template_id');
        });
    }
};
