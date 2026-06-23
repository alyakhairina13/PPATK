<?php

namespace App\Models;

use Database\Factories\TemplateAktaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateAkta extends Model
{
    /** @use HasFactory<TemplateAktaFactory> */
    use HasFactory;

    protected $table = 'template_akta';
    protected $primaryKey = 'id_template_akta';

    protected $fillable = [
        'title',
        'slug',
        'original_filename',
        'file_extension',
        'file_path',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    protected static function newFactory(): TemplateAktaFactory
    {
        return TemplateAktaFactory::new();
    }

    public function akta(): HasMany
    {
        return $this->hasMany(Akta::class, 'template_id', 'id_template_akta');
    }
}
