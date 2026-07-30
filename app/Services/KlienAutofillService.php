<?php

namespace App\Services;

use App\Models\Klien;

class KlienAutofillService
{
    /**
     * Placeholder prefixes whose groups are automatically populated from the
     * klien record selected as Pihak 1 / Pihak 2 on the akta form
     * (e.g. `{{$dpihak1_nama}}`, `{{$dpihak2_alamat}}`).
     */
    public const AUTOFILL_PREFIXES = ['dpihak1', 'dpihak2'];

    /**
     * Prefixes whose fields are LOCKED (read-only) on the form because they
     * always mirror the selected klien record verbatim. Pihak 1 is the
     * primary party and its data must stay in sync with the klien master.
     */
    public const LOCKED_PREFIXES = ['dpihak1'];

    /**
     * Mapping of a tag suffix (the part of the tag name that follows the
     * group prefix) to the klien attribute that supplies its value.
     *
     * Any tag whose prefix is one of AUTOFILL_PREFIXES and whose suffix
     * appears here is treated as auto-filled and locked on the form.
     *
     * @var array<string, string>
     */
    public const SUFFIX_TO_FIELD = [
        'nama' => 'nama_lengkap',
        'name' => 'nama_lengkap',
        'nama_lengkap' => 'nama_lengkap',
        'full_name' => 'nama_lengkap',
        'nik' => 'nik',
        'id_number' => 'nik',
        'nomor_ktp' => 'nik',
        'ktp' => 'nik',
        'tempat_tanggal_lahir' => 'tempat_tanggal_lahir',
        'ttl' => 'tempat_tanggal_lahir',
        'birth_date' => 'tempat_tanggal_lahir',
        'tempat_lahir' => 'tempat_tanggal_lahir',
        'tanggal_lahir' => 'tempat_tanggal_lahir',
        'jenis_kelamin' => 'jenis_kelamin',
        'gender' => 'jenis_kelamin',
        'jk' => 'jenis_kelamin',
        'alamat' => 'alamat',
        'address' => 'alamat',
        'nomor_telepon' => 'nomor_telepon',
        'telepon' => 'nomor_telepon',
        'phone' => 'nomor_telepon',
        'no_telp' => 'nomor_telepon',
        'no_hp' => 'nomor_telepon',
        'pekerjaan' => 'pekerjaan',
        'occupation' => 'pekerjaan',
        'job' => 'pekerjaan',
        'npwp' => 'npwp',
    ];

    /**
     * Whether a placeholder tag belongs to a Pihak 1 / Pihak 2 group.
     */
    public function isPihakTag(string $tag): bool
    {
        return in_array(TemplateAktaService::groupPrefixForTag($tag), self::AUTOFILL_PREFIXES, true);
    }

    /**
     * Whether a tag is auto-filled (maps to a known klien attribute).
     * Covers both locked (Pihak 1) and pre-fillable (Pihak 2) tags.
     */
    public function isAutofillTag(string $tag): bool
    {
        return $this->fieldForTag($tag) !== null;
    }

    /**
     * Whether a tag is LOCKED (read-only) on the form because it mirrors the
     * Pihak 1 klien record.
     */
    public function isLockedTag(string $tag): bool
    {
        $prefix = $this->prefixForTag($tag);

        return $prefix !== null && in_array($prefix, self::LOCKED_PREFIXES, true);
    }

    /**
     * Resolve the group prefix of a pihak tag, or null when it is not one.
     */
    public function prefixForTag(string $tag): ?string
    {
        $prefix = TemplateAktaService::groupPrefixForTag($tag);

        return in_array($prefix, self::AUTOFILL_PREFIXES, true) ? $prefix : null;
    }

    /**
     * Resolve the klien attribute backing a tag, or null when the tag is not
     * an auto-filled pihak tag.
     */
    public function fieldForTag(string $tag): ?string
    {
        $prefix = $this->prefixForTag($tag);

        if ($prefix === null) {
            return null;
        }

        $suffix = ltrim(substr($tag, strlen($prefix)), '_');

        return self::SUFFIX_TO_FIELD[$suffix] ?? null;
    }

    /**
     * The full suffix -> klien-attribute map, used to drive the client-side
     * auto-fill on the akta form.
     *
     * @return array<string, string>
     */
    public function suffixFieldMap(): array
    {
        return self::SUFFIX_TO_FIELD;
    }

    /**
     * Resolve the value for a single pihak tag from the given klien.
     */
    public function resolveTagValue(string $tag, ?Klien $klien): string
    {
        $field = $this->fieldForTag($tag);

        if ($field === null || $klien === null) {
            return '';
        }

        return (string) ($klien->{$field} ?? '');
    }

    /**
     * Resolve the value for a pihak tag, selecting the Pihak 1 or Pihak 2
     * klien according to the tag prefix.
     */
    public function resolveTagValueForClients(string $tag, ?Klien $pihak1, ?Klien $pihak2): string
    {
        $prefix = $this->prefixForTag($tag);

        return $this->resolveTagValue($tag, $prefix === 'dpihak2' ? $pihak2 : $pihak1);
    }

    /**
     * Build the locked payload (tag => value) for the LOCKED pihak tags
     * (Pihak 1 only). Pihak 2 tags are NOT included here because their
     * values come from form input (pre-filled by JS but manually editable).
     *
     * @param  array<int, string>  $tags
     * @return array<string, string>
     */
    public function lockedValuesForTags(array $tags, ?Klien $pihak1, ?Klien $pihak2): array
    {
        $values = [];

        foreach ($tags as $tag) {
            if (! $this->isLockedTag($tag) || $this->fieldForTag($tag) === null) {
                continue;
            }

            $values[$tag] = $this->resolveTagValueForClients($tag, $pihak1, $pihak2);
        }

        return $values;
    }
}
