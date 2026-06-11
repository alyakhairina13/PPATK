<?php

namespace Database\Factories;

use App\Models\Klien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Klien>
 */
class KlienFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'nik' => fake()->unique()->numerify('################'), // 16 digits
            'tempat_tanggal_lahir' => fake()->city() . ', ' . fake()->date('d-m-Y'),
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'alamat' => fake()->address(),
            'nomor_telepon' => fake()->phoneNumber(),
            'pekerjaan' => fake()->jobTitle(),
            'npwp' => fake()->optional()->numerify('##.###.###.#-###.###'),
        ];
    }
}
