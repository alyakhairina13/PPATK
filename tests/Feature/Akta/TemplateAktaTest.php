<?php

use App\Models\Akta;
use App\Models\Klien;
use App\Models\TemplateAkta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('templates management page renders alias management with dynamic groups', function () {
    $user = User::factory()->create();

    TemplateAkta::factory()->create([
        'tags' => ['dppat_name', 'dseller_name', 'dpihak1_nama', 'dpihak2_nama', 'keterangan'],
    ]);

    $response = $this->actingAs($user)->get(route('akta.templates.index'));

    $response->assertOk();
    $response->assertSee('Kelola Alias Label');
    $response->assertSee('Data PPAT');
    $response->assertSee('Data Penjual');
    $response->assertSee('Pihak 1');
    $response->assertSee('Pihak 2');
    $response->assertSee('Data Lainnya');
    $response->assertSee('Nama PPAT');
});

test('can upload template akta and extract tags', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent(
        'ajb-template.doc',
        '<html><body>{{$nama_penjual}} {{ mb_strtoupper($nama_pembeli) }}</body></html>'
    );

    $response = $this->actingAs($user)->post(route('akta.templates.store'), [
        'title' => 'Akta AJB Rumah',
        'template_file' => $file,
    ]);

    $response->assertRedirect(route('akta.templates.index'));

    $template = TemplateAkta::query()->where('title', 'Akta AJB Rumah')->first();

    expect($template)->not->toBeNull();
    expect($template->tags)->toBe(['nama_pembeli', 'nama_penjual']);

    Storage::disk('local')->assertExists($template->file_path);
});

test('can download merged akta document from template data', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $klien = Klien::factory()->create();

    Storage::disk('local')->put(
        'templates/akta-akta-ajb-rumah.doc',
        '<html><body>Penjual: {{$nama_penjual}} | Pembeli: {{ mb_strtoupper($nama_pembeli) }}</body></html>'
    );

    $template = TemplateAkta::factory()->create([
        'title' => 'Akta AJB Rumah',
        'slug' => 'akta-ajb-rumah',
        'original_filename' => 'akta-ajb-rumah.doc',
        'file_extension' => 'doc',
        'file_path' => 'templates/akta-akta-ajb-rumah.doc',
        'tags' => ['nama_penjual', 'nama_pembeli'],
    ]);

    $akta = Akta::factory()->create([
        'id_klien' => $klien->id_klien,
        'id_user' => $user->id_user,
        'template_id' => $template->id_template_akta,
        'jenis_template' => $template->title,
        'konten_teks_utama' => json_encode([
            'nama_penjual' => 'Budi',
            'nama_pembeli' => 'Sinta',
        ], JSON_UNESCAPED_UNICODE),
    ]);

    $response = $this->actingAs($user)->get(route('akta.download', $akta->id_akta));

    $response->assertOk();
    $response->assertDownload('akta-'.str_pad((string) $akta->id_akta, 4, '0', STR_PAD_LEFT).'-'.$template->slug.'.doc');

    $downloadedFile = $response->baseResponse->getFile()->getPathname();
    $downloadedContent = file_get_contents($downloadedFile);

    expect($downloadedContent)->toContain('Penjual: Budi');
    expect($downloadedContent)->toContain('Pembeli: SINTA');
});

test('can delete unused template akta', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    Storage::disk('local')->put('templates/akta-to-delete.doc', '<html><body>{{$nama}}</body></html>');

    $template = TemplateAkta::factory()->create([
        'file_path' => 'templates/akta-to-delete.doc',
        'file_extension' => 'doc',
    ]);

    $response = $this->actingAs($user)->delete(route('akta.templates.destroy', $template->id_template_akta));

    $response->assertRedirect(route('akta.templates.index'));
    $this->assertDatabaseMissing('template_akta', [
        'id_template_akta' => $template->id_template_akta,
    ]);
    Storage::disk('local')->assertMissing('templates/akta-to-delete.doc');
});

test('cannot delete template akta that is already used by akta', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $template = TemplateAkta::factory()->create();

    Akta::factory()->create([
        'template_id' => $template->id_template_akta,
        'jenis_template' => $template->title,
    ]);

    $response = $this->actingAs($user)->delete(route('akta.templates.destroy', $template->id_template_akta));

    $response->assertRedirect(route('akta.templates.index'));
    $this->assertDatabaseHas('template_akta', [
        'id_template_akta' => $template->id_template_akta,
    ]);
});
