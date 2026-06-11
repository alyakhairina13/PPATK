<?php

namespace Database\Factories;

use App\Models\LampiranDokumen;
use App\Models\Akta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LampiranDokumen>
 */
class LampiranDokumenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'pdf']);
        $filename = fake()->uuid() . '.' . $extension;
        
        return [
            'id_akta' => Akta::factory(),
            'nama_file' => fake()->word() . '.' . $extension,
            'format_extension' => $extension,
            'ukuran_berkas' => fake()->randomFloat(2, 0.5, 9.99), // MB
            'path_penyimpanan' => 'lampiran/' . $filename,
        ];
    }
}
