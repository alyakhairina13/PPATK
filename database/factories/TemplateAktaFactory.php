<?php

namespace Database\Factories;

use App\Models\TemplateAkta;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TemplateAkta>
 */
class TemplateAktaFactory extends Factory
{
    protected $model = TemplateAkta::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);
        $slug = Str::slug($title);

        return [
            'title' => Str::title($title),
            'slug' => $slug,
            'original_filename' => 'template.doc',
            'file_extension' => 'doc',
            'file_path' => 'templates/akta-'.$slug.'.doc',
            'tags' => ['nama', 'alamat'],
        ];
    }
}
