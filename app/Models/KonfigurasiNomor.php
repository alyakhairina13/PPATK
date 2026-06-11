<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiNomor extends Model
{
    protected $table = 'konfigurasi_nomor';

    protected $fillable = [
        'pattern',
        'reset_period',
        'starting_number',
    ];

    protected function casts(): array
    {
        return [
            'starting_number' => 'integer',
        ];
    }

    public function generatePreview(): string
    {
        $nomor = str_pad($this->starting_number, 3, '0', STR_PAD_LEFT);
        $tahun = date('Y');
        $bulan = date('m');

        return str_replace(
            ['{NOMOR}', '{TAHUN}', '{BULAN}'],
            [$nomor, $tahun, $bulan],
            $this->pattern
        );
    }
}
