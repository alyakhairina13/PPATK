<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionHistory extends Model
{
    use HasFactory;

    protected $table = 'version_history';
    protected $primaryKey = 'id_version';
    public $timestamps = false;

    protected $fillable = [
        'id_akta',
        'versi_ke',
        'backup_konten_teks',
        'timestamp_perubahan',
        'diubah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'timestamp_perubahan' => 'datetime',
        ];
    }

    public function akta(): BelongsTo
    {
        return $this->belongsTo(Akta::class, 'id_akta');
    }
}
