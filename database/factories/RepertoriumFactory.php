<?php

namespace Database\Factories;

use App\Models\Akta;
use App\Models\Repertorium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repertorium>
 */
class RepertoriumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timestamp = now();
        
        return [
            'id_akta' => Akta::factory(),
            'nomor_akta_resmi' => fake()->unique()->numerify('AKT-####-####'),
            'indeks_urutan' => fake()->numberBetween(1, 1000),
            'bulan_buku' => $timestamp->month,
            'tahun_buku' => $timestamp->year,
            'timestamp_generasi' => $timestamp,
        ];
    }
}
