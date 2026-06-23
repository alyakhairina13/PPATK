<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Akta extends Model
{
    use HasFactory;

    protected $table = 'akta';
    protected $primaryKey = 'id_akta';

    protected $fillable = [
        'id_klien',
        'id_user',
        'template_id',
        'jenis_template',
        'konten_teks_utama',
        'status_workflow',
        'tanggal_dibuat',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dibuat' => 'datetime',
            'last_updated' => 'datetime',
        ];
    }

    public function klien(): BelongsTo
    {
        return $this->belongsTo(Klien::class, 'id_klien');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function templateAkta(): BelongsTo
    {
        return $this->belongsTo(TemplateAkta::class, 'template_id', 'id_template_akta');
    }

    public function lampiran(): HasMany
    {
        return $this->hasMany(LampiranDokumen::class, 'id_akta');
    }

    public function versionHistory(): HasMany
    {
        return $this->hasMany(VersionHistory::class, 'id_akta');
    }

    public function repertorium(): HasOne
    {
        return $this->hasOne(Repertorium::class, 'id_akta');
    }

    public function isDraft(): bool
    {
        return $this->status_workflow === 'Draft';
    }

    public function isFinal(): bool
    {
        return $this->status_workflow === 'Final';
    }

    public function isSelesai(): bool
    {
        return $this->status_workflow === 'Selesai';
    }

    /**
     * @return array<string, string>
     */
    public function getContentFieldsAttribute(): array
    {
        $decoded = json_decode($this->konten_teks_utama, true);

        if (! is_array($decoded)) {
            return [];
        }

        $fields = [];

        foreach ($decoded as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $fields[$key] = is_scalar($value) || $value === null
                ? (string) $value
                : '';
        }

        return $fields;
    }
}
