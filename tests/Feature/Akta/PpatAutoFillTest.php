<?php

use App\Models\Akta;
use App\Models\Klien;
use App\Models\TemplateAkta;
use App\Models\User;
use App\Services\PpatConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $path = storage_path('app/private/config/ppat.json');

    if (file_exists($path)) {
        unlink($path);
    }

    app()->forgetInstance(PpatConfigurationService::class);
});

afterEach(function () {
    $path = storage_path('app/private/config/ppat.json');

    if (file_exists($path)) {
        unlink($path);
    }
});

function configurePpat(array $values): void
{
    app(PpatConfigurationService::class)->updateConfiguration($values);
    app()->forgetInstance(PpatConfigurationService::class);
}

test('akta store auto-fills dppat fields from PPAT configuration', function () {
    Storage::fake('local');

    configurePpat([
        'dppat_name' => 'Dr. Wiga Angraini, S.H.',
        'dppat_work_area' => 'Kota Denpasar',
    ]);

    $template = TemplateAkta::factory()->create([
        'tags' => ['dppat_name', 'dppat_work_area', 'dseller_name'],
        'file_extension' => 'doc',
    ]);
    Storage::disk('local')->put($template->file_path, '<html>{{$dppat_name}}</html>');

    $user = User::factory()->create();
    $klien = Klien::factory()->create();

    $this->actingAs($user)->post('/akta', [
        'id_klien' => $klien->id_klien,
        'template_id' => $template->id_template_akta,
        'template_fields' => [
            'dppat_name' => '',
            'dppat_work_area' => '',
            'dseller_name' => 'Budi',
        ],
    ])->assertRedirect();

    $content = json_decode(Akta::first()->konten_teks_utama, true);

    expect($content['dppat_name'])->toBe('Dr. Wiga Angraini, S.H.')
        ->and($content['dppat_work_area'])->toBe('Kota Denpasar')
        ->and($content['dseller_name'])->toBe('Budi');
});

test('akta create form locks dppat fields and pre-fills them from configuration', function () {
    Storage::fake('local');

    configurePpat(['dppat_name' => 'Dr. Wiga Angraini, S.H.']);

    $template = TemplateAkta::factory()->create([
        'tags' => ['dppat_name', 'dseller_name'],
        'file_extension' => 'doc',
    ]);
    Storage::disk('local')->put($template->file_path, '<html>{{$dppat_name}}</html>');

    $response = $this->actingAs(User::factory()->create())->get('/akta/create');

    $response->assertOk();
    $response->assertSee('Dr. Wiga Angraini, S.H.', false);
    $response->assertSee('readonly', false);
});

test('any dppat-prefixed field configured dynamically is auto-filled', function () {
    Storage::fake('local');

    configurePpat([
        'dppat_nama' => 'Dr. Wiga',
        'dppat_wilayah_kerja' => 'Kota Denpasar',
    ]);

    $template = TemplateAkta::factory()->create([
        'tags' => ['dppat_nama', 'dppat_wilayah_kerja', 'dpihak1_nama'],
        'file_extension' => 'doc',
    ]);
    Storage::disk('local')->put($template->file_path, '<html>{{$dppat_nama}}</html>');

    $this->actingAs(User::factory()->create())->post('/akta', [
        'id_klien' => Klien::factory()->create()->id_klien,
        'template_id' => $template->id_template_akta,
        'template_fields' => [
            'dppat_nama' => '',
            'dppat_wilayah_kerja' => '',
            'dpihak1_nama' => 'Andi',
        ],
    ])->assertRedirect();

    $content = json_decode(Akta::first()->konten_teks_utama, true);

    expect($content['dppat_nama'])->toBe('Dr. Wiga')
        ->and($content['dppat_wilayah_kerja'])->toBe('Kota Denpasar')
        ->and($content['dpihak1_nama'])->toBe('Andi');
});

test('konfigurasi page lists detected dppat fields and persists their values', function () {
    Storage::fake('local');

    TemplateAkta::factory()->create(['tags' => ['dppat_name', 'dpihak1_nama']]);
    $notaris = User::factory()->notaris()->create();

    $response = $this->actingAs($notaris)->get('/konfigurasi');

    $response->assertOk();
    $response->assertSee('name="ppat_values[dppat_name]"', false);

    $this->actingAs($notaris)->put('/konfigurasi', [
        'pattern' => '{NOMOR}/{TAHUN}',
        'reset_period' => 'tahunan',
        'starting_number' => 1,
        'ppat_values' => ['dppat_name' => 'Dr. Wiga'],
    ])->assertRedirect();

    expect(app(PpatConfigurationService::class)->getConfiguration()['dppat_name'])->toBe('Dr. Wiga');
});

