<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'nama_lengkap' => fake()->name(),
            'role' => 'AdminStaff',
            'nip_staff' => fake()->numerify('NIP################'),
            'no_sertifikat_notaris' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a Notaris.
     */
    public function notaris(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'Notaris',
            'nip_staff' => null,
            'no_sertifikat_notaris' => fake()->numerify('NOT################'),
        ]);
    }

    /**
     * Indicate that the user is an AdminStaff.
     */
    public function adminStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'AdminStaff',
            'nip_staff' => fake()->numerify('NIP################'),
            'no_sertifikat_notaris' => null,
        ]);
    }
}
