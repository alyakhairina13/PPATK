<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_akta', function (Blueprint $table) {
            $table->id('id_template_akta');
            $table->string('title', 150)->unique();
            $table->string('slug', 180)->unique();
            $table->string('original_filename', 255);
            $table->string('file_extension', 10);
            $table->string('file_path', 255);
            $table->json('tags');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_akta');
    }
};
