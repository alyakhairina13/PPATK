<?php

use App\Services\TemplateAktaService;

it('detects the prefix dynamically from the leading token before the first underscore', function () {
    expect(TemplateAktaService::detectPrefix('dppat_name'))->toBe('dppat')
        ->and(TemplateAktaService::detectPrefix('dseller_name'))->toBe('dseller')
        ->and(TemplateAktaService::detectPrefix('dland_nib'))->toBe('dland')
        ->and(TemplateAktaService::detectPrefix('some_weird_tag'))->toBe('some');
});

it('keeps trailing digits so numbered prefixes stay distinct', function () {
    expect(TemplateAktaService::detectPrefix('dpihak1_nama'))->toBe('dpihak1')
        ->and(TemplateAktaService::detectPrefix('dpihak2_nama'))->toBe('dpihak2')
        ->and(TemplateAktaService::detectPrefix('dwitness1_name'))->toBe('dwitness1')
        ->and(TemplateAktaService::detectPrefix('dwitness2_name'))->toBe('dwitness2');
});

it('returns null for tags without a detectable prefix', function () {
    expect(TemplateAktaService::detectPrefix('keterangan'))->toBeNull()
        ->and(TemplateAktaService::detectPrefix('_leading'))->toBeNull()
        ->and(TemplateAktaService::groupPrefixForTag('keterangan'))->toBe('lainnya');
});

it('keeps numbered prefixes in separate groups', function () {
    $grouped = TemplateAktaService::groupTagsByPrefix([
        'dpihak1_nama',
        'dpihak1_alamat',
        'dpihak2_nama',
        'dpihak2_alamat',
        'dppat_name',
        'keterangan',
    ]);

    expect(array_keys($grouped))->toBe(['dpihak1', 'dpihak2', 'dppat', 'lainnya'])
        ->and($grouped['dpihak1'])->toBe(['dpihak1_nama', 'dpihak1_alamat'])
        ->and($grouped['dpihak2'])->toBe(['dpihak2_nama', 'dpihak2_alamat']);
});
