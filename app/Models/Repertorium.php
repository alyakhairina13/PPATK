<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repertorium extends Model
{
    use HasFactory;

    protected $table = 'repertorium';
    protected $primaryKey = 'id_repertorium';
    public $timestamps = false;

    protected $fillable = [
        'id_akta',
        'nomor_akta_resmi',
        'indeks_urutan',
        'bulan_buku',
        'tahun_buku',
        'timestamp_generasi',
    ];

    protected function casts(): array
    {
        return [
            'indeks_urutan' => 'integer',
            'timestamp_generasi' => 'datetime',
        ];
    }

    public function akta(): BelongsTo
    {
        return $this->belongsTo(Akta::class, 'id_akta');
    }
}
