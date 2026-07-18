<?php

use App\Services\TagAliasService;
use App\Services\TemplateAktaService;

beforeEach(function () {
    $path = storage_path('app/private/config/tag_aliases.json');

    if (file_exists($path)) {
        unlink($path);
    }

    app()->forgetInstance(TagAliasService::class);
});

afterEach(function () {
    $path = storage_path('app/private/config/tag_aliases.json');

    if (file_exists($path)) {
        unlink($path);
    }
});

it('maps group prefixes to their configured labels with a fallback', function () {
    expect(TemplateAktaService::groupLabelForPrefix('dppat'))->toBe('Data PPAT')
        ->and(TemplateAktaService::groupLabelForPrefix('dseller'))->toBe('Data Penjual')
        ->and(TemplateAktaService::groupLabelForPrefix('dwitness1'))->toBe('Saksi 1')
        ->and(TemplateAktaService::groupLabelForPrefix('dpihak1'))->toBe('Pihak 1')
        ->and(TemplateAktaService::groupLabelForPrefix('dpihak2'))->toBe('Pihak 2')
        ->and(TemplateAktaService::groupLabelForPrefix('lainnya'))->toBe('Data Lainnya')
        ->and(TemplateAktaService::groupLabelForPrefix('dunknown'))->toBe('Data Unknown');
});

it('resolves explicit alias labels for known tags', function () {
    expect(TemplateAktaService::labelForTag('dppat_name'))->toBe('Nama PPAT')
        ->and(TemplateAktaService::labelForTag('dppat_work_area'))->toBe('Wilayah Kerja')
        ->and(TemplateAktaService::labelForTag('dwitness1_name'))->toBe('Nama')
        ->and(TemplateAktaService::labelForTag('dwitness2_address'))->toBe('Alamat')
        ->and(TemplateAktaService::labelForTag('dland_nib'))->toBe('NIB')
        ->and(TemplateAktaService::labelForTag('dtrx_price_in_words'))->toBe('Harga (Terbilang)');
});

it('derives a CamelCase label for tags without an alias', function () {
    expect(TemplateAktaService::labelForTag('dppat_work_area_snake'))->toBe('Work Area Snake')
        ->and(TemplateAktaService::labelForTag('dpihak1_tempatLahir'))->toBe('Tempat Lahir')
        ->and(TemplateAktaService::labelForTag('dpihak1_nama'))->toBe('Nama');
});

it('reflects alias edits made through the service', function () {
    app(TagAliasService::class)->update(
        ['dppat' => 'Data Pejabat'],
        ['dppat_name' => 'Nama Lengkap PPAT'],
    );

    app()->forgetInstance(TagAliasService::class);

    expect(TemplateAktaService::groupLabelForPrefix('dppat'))->toBe('Data Pejabat')
        ->and(TemplateAktaService::labelForTag('dppat_name'))->toBe('Nama Lengkap PPAT');
});

it('persists aliases to the config file', function () {
    app(TagAliasService::class)->update(['dppat' => 'Data PPAT Khusus'], []);

    expect(file_get_contents(storage_path('app/private/config/tag_aliases.json')))
        ->toContain('Data PPAT Khusus');
});
