<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TagAliasService
{
    private ?array $cache = null;

    /**
     * Default group (prefix) aliases used to seed the configuration file
     * on first use. These mirror the canonical AJB data groupings.
     *
     * @return array<string, string>
     */
    public function defaultPrefixAliases(): array
    {
        return [
            'dppat' => 'Data PPAT',
            'dseller' => 'Data Penjual',
            'dbuyer' => 'Data Pembeli',
            'dwitness1' => 'Saksi 1',
            'dwitness2' => 'Saksi 2',
            'dpihak1' => 'Pihak 1',
            'dpihak2' => 'Pihak 2',
            'dland' => 'Data Objek Tanah',
            'dlocation' => 'Data Lokasi',
            'dtrx' => 'Data Transaksi',
        ];
    }

    /**
     * Default field (tag) aliases used to seed the configuration file.
     *
     * @return array<string, string>
     */
    public function defaultTagAliases(): array
    {
        return [
            'dppat_name' => 'Nama PPAT',
            'dppat_work_area' => 'Wilayah Kerja',
            'dppat_appointment_number' => 'Nomor Pengangkatan',
            'dppat_appointment_date' => 'Tanggal Pengangkatan',
            'dppat_office_address' => 'Alamat Kantor',

            'dseller_name' => 'Nama Penjual',
            'dseller_birth_date' => 'Tempat/Tanggal Lahir Penjual',
            'dseller_id_number' => 'Nomor KTP Penjual',
            'dseller_occupation' => 'Pekerjaan Penjual',
            'dseller_address' => 'Alamat Penjual',
            'dseller_basis' => 'Dasar Perolehan Hak',
            'dseller_rights_division' => 'Pembagian Hak',

            'dbuyer_name' => 'Nama Pembeli',
            'dbuyer_birth_date' => 'Tempat/Tanggal Lahir Pembeli',
            'dbuyer_id_number' => 'Nomor KTP Pembeli',
            'dbuyer_occupation' => 'Pekerjaan Pembeli',
            'dbuyer_address' => 'Alamat Pembeli',

            'dwitness1_name' => 'Nama',
            'dwitness1_birth_date' => 'Tempat/Tanggal Lahir',
            'dwitness1_id_number' => 'Nomor KTP',
            'dwitness1_occupation' => 'Pekerjaan',
            'dwitness1_address' => 'Alamat',
            'dwitness2_name' => 'Nama',
            'dwitness2_birth_date' => 'Tempat/Tanggal Lahir',
            'dwitness2_id_number' => 'Nomor KTP',
            'dwitness2_occupation' => 'Pekerjaan',
            'dwitness2_address' => 'Alamat',

            'dland_certificate_number' => 'Nomor Sertifikat',
            'dland_certificate_page' => 'Halaman Sertifikat',
            'dland_survey_number' => 'Nomor Surat Ukur',
            'dland_survey_page' => 'Halaman Surat Ukur',
            'dland_survey_date' => 'Tanggal Surat Ukur',
            'dland_area' => 'Luas Tanah (m²)',
            'dland_area_in_words' => 'Luas Tanah (Terbilang)',
            'dland_nib' => 'NIB',

            'dlocation_province' => 'Provinsi',
            'dlocation_regency' => 'Kabupaten/Kota',
            'dlocation_district' => 'Kecamatan',
            'dlocation_village' => 'Kelurahan/Desa',
            'dlocation_street' => 'Jalan',
            'dlocation_court_office' => 'Pengadilan Negeri',
            'dlocation_land_office_city' => 'Kantor Pertanahan',

            'dtrx_day' => 'Hari',
            'dtrx_date' => 'Tanggal',
            'dtrx_price_number' => 'Harga (Angka)',
            'dtrx_price_in_words' => 'Harga (Terbilang)',
            'dtrx_statement_date' => 'Tanggal Pernyataan',
        ];
    }

    /**
     * @return array{prefixes: array<string, string>, tags: array<string, string>}
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $path = $this->getFilePath();

        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);

            if (is_array($decoded)) {
                return $this->cache = [
                    'prefixes' => $this->normalise($decoded['prefixes'] ?? []),
                    'tags' => $this->normalise($decoded['tags'] ?? []),
                ];
            }
        }

        $seeded = [
            'prefixes' => $this->defaultPrefixAliases(),
            'tags' => $this->defaultTagAliases(),
        ];

        $this->write($seeded['prefixes'], $seeded['tags']);

        return $this->cache = $seeded;
    }

    /**
     * @return array<string, string>
     */
    public function prefixAliases(): array
    {
        return $this->all()['prefixes'];
    }

    /**
     * @return array<string, string>
     */
    public function tagAliases(): array
    {
        return $this->all()['tags'];
    }

    public function prefixLabel(string $prefix): ?string
    {
        $aliases = $this->prefixAliases();

        if (isset($aliases[$prefix]) && $aliases[$prefix] !== '') {
            return $aliases[$prefix];
        }

        return null;
    }

    public function tagLabel(string $tag): ?string
    {
        $aliases = $this->tagAliases();

        if (isset($aliases[$tag]) && $aliases[$tag] !== '') {
            return $aliases[$tag];
        }

        return null;
    }

    /**
     * @param  array<string, string>  $prefixes
     * @param  array<string, string>  $tags
     */
    public function update(array $prefixes, array $tags): void
    {
        $this->write($this->normalise($prefixes), $this->normalise($tags));
        $this->cache = null;
    }

    private function getFilePath(): string
    {
        return storage_path('app/private/config/tag_aliases.json');
    }

    /**
     * @param  array<string|int, mixed>  $input
     * @return array<string, string>
     */
    private function normalise(array $input): array
    {
        $clean = [];

        foreach ($input as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $value = is_string($value) ? trim($value) : '';

            if ($value === '') {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * @param  array<string, string>  $prefixes
     * @param  array<string, string>  $tags
     */
    private function write(array $prefixes, array $tags): void
    {
        $path = $this->getFilePath();
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0777, true);
        }

        File::put(
            $path,
            json_encode(
                ['prefixes' => $prefixes, 'tags' => $tags],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     * Human readable fallback label for a prefix that has no explicit alias.
     * Numbered prefixes (e.g. `dpihak1`) become "Pihak 1"; unnumbered ones
     * follow the `d` + entity convention, e.g. `dppat` -> "Data Ppat".
     */
    public function fallbackPrefixLabel(string $prefix): string
    {
        if ($prefix === 'lainnya') {
            return 'Data Lainnya';
        }

        $body = $prefix;

        if (str_starts_with($body, 'd') && strlen($body) > 1) {
            $body = substr($body, 1);
        }

        if (preg_match('/^(.+?)(\d+)$/', $body, $m)) {
            return Str::title(Str::camel($m[1])).' '.$m[2];
        }

        return 'Data '.Str::title(Str::camel($body));
    }
}
