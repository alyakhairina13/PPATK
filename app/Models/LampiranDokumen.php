<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LampiranDokumen extends Model
{
    use HasFactory;

    protected $table = 'lampiran_dokumen';
    protected $primaryKey = 'id_dokumen';
    public $timestamps = false;

    protected $fillable = [
        'id_akta',
        'nama_file',
        'format_extension',
        'ukuran_berkas',
        'path_penyimpanan',
    ];

    protected function casts(): array
    {
        return [
            'ukuran_berkas' => 'decimal:2',
        ];
    }

    public function akta(): BelongsTo
    {
        return $this->belongsTo(Akta::class, 'id_akta');
    }
}
