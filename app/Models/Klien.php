<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Klien extends Model
{
    use HasFactory;

    protected $table = 'klien';
    protected $primaryKey = 'id_klien';
    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap',
        'nik',
        'tempat_tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'nomor_telepon',
        'pekerjaan',
        'npwp',
    ];

    public function akta(): HasMany
    {
        return $this->hasMany(Akta::class, 'id_klien');
    }
}
