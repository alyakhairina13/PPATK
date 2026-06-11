<?php

use App\Models\User;
use App\Models\Akta;
use App\Models\Klien;
use App\Models\Repertorium;
use App\Models\VersionHistory;
use App\Models\LampiranDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('akta index page is accessible', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/akta');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.akta.index');
});

test('can list akta with filters', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(5)->create(['status_workflow' => 'Draft']);
    Akta::factory()->count(3)->create(['status_workflow' => 'Final']);
    
    $response = $this->actingAs($user)->get('/akta?status_workflow=Draft');
    
    $response->assertStatus(200);
    $response->assertViewHas('aktas', function ($aktas) {
        return $aktas->where('status_workflow', 'Draft')->count() === 5;
    });
});

test('can create new akta draft', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create();
    
    $aktaData = [
        'id_klien' => $klien->id_klien,
        'jenis_template' => 'AJB',
        'konten_teks_utama' => 'This is the content of the akta.',
    ];
    
    $response = $this->actingAs($user)->post('/akta', $aktaData);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('akta', [
        'id_klien' => $klien->id_klien,
        'id_user' => $user->id_user,
        'status_workflow' => 'Draft',
        'jenis_template' => 'AJB',
    ]);

    $akta = Akta::where('id_klien', $klien->id_klien)->first();
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/submit-verification");
    
    $response->assertRedirect();
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
        'status_workflow' => 'Diverifikasi',
    ]);
});

test('AdminStaff cannot set akta to Final', function () {
    $user = User::factory()->adminStaff()->create();
    $akta = Akta::factory()->diverifikasi()->create();
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/set-final");
    
    $response->assertStatus(403);
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
        'status_workflow' => 'Diverifikasi',
    ]);
});

test('Notaris can set akta to Final', function () {
    $user = User::factory()->notaris()->create();
    $akta = Akta::factory()->diverifikasi()->create();
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/set-final");
    
    $response->assertRedirect();
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
        'status_workflow' => 'Final',
    ]);
});

test('Final status generates repertorium', function () {
    $user = User::factory()->notaris()->create();
    $akta = Akta::factory()->diverifikasi()->create();
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/set-final");
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('repertorium', [
        'id_akta' => $akta->id_akta,
    ]);
    
    $repertorium = Repertorium::where('id_akta', $akta->id_akta)->first();
    expect($repertorium->nomor_akta_resmi)->not->toBeNull();
    expect($repertorium->bulan_buku)->toBe(date('m'));
    expect($repertorium->tahun_buku)->toBe(date('Y'));
});

test('Notaris can set akta to Selesai', function () {
    $user = User::factory()->notaris()->create();
    $akta = Akta::factory()->final()->create();
    Repertorium::factory()->create(['id_akta' => $akta->id_akta]);
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/set-selesai");
    
    $response->assertRedirect();
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
        'status_workflow' => 'Selesai',
    ]);
});

test('Selesai akta is read-only', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->selesai()->create();
    
    $updatedData = [
        'id_klien' => $akta->id_klien,
        'jenis_template' => $akta->jenis_template,
        'konten_teks_utama' => 'Trying to update completed akta',
    ];
    
    $response = $this->actingAs($user)->put("/akta/{$akta->id_akta}", $updatedData);
    
    $response->assertStatus(403);
    $this->assertDatabaseMissing('akta', [
        'id_akta' => $akta->id_akta,
        'konten_teks_utama' => 'Trying to update completed akta',
    ]);
});

test('can delete akta', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->draft()->create();
    
    $response = $this->actingAs($user)->delete("/akta/{$akta->id_akta}");
    
    $response->assertRedirect('/akta');
    $this->assertDatabaseMissing('akta', [
        'id_akta' => $akta->id_akta,
    ]);
});

test('can upload lampiran', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $akta = Akta::factory()->draft()->create();
    
    $file = UploadedFile::fake()->image('document.jpg', 800, 600);
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/lampiran", [
        'file' => $file,
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('lampiran_dokumen', [
        'id_akta' => $akta->id_akta,
        'format_extension' => 'jpg',
    ]);
});

test('lampiran must be JPG PNG or PDF', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $akta = Akta::factory()->draft()->create();
    
    $file = UploadedFile::fake()->create('document.txt', 100);
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/lampiran", [
        'file' => $file,
    ]);
    
    $response->assertSessionHasErrors('file');
});

test('lampiran must be under 10MB', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $akta = Akta::factory()->draft()->create();
    
    $file = UploadedFile::fake()->create('document.pdf', 11000);
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/lampiran", [
        'file' => $file,
    ]);
    
    $response->assertSessionHasErrors('file');
});

test('can filter akta by jenis template', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(3)->create(['jenis_template' => 'AJB']);
    Akta::factory()->count(2)->create(['jenis_template' => 'Perjanjian']);
    
    $response = $this->actingAs($user)->get('/akta?jenis_template=AJB');
    
    $response->assertStatus(200);
    $response->assertViewHas('aktas', function ($aktas) {
        return $aktas->where('jenis_template', 'AJB')->count() === 3;
    });
});

test('can revert akta to draft from diverifikasi', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->diverifikasi()->create();
    
    $response = $this->actingAs($user)->post("/akta/{$akta->id_akta}/revert-draft");
    
    $response->assertRedirect();
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
        'status_workflow' => 'Draft',
    ]);
});

test('cannot delete final or selesai akta', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->final()->create();
    
    $response = $this->actingAs($user)->delete("/akta/{$akta->id_akta}");
    
    $response->assertStatus(403);
    $this->assertDatabaseHas('akta', [
        'id_akta' => $akta->id_akta,
    ]);
});

test('can view akta detail with related data', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    $akta = Akta::factory()->create([
        'id_klien' => $klien->id_klien,
        'jenis_template' => 'AJB',
    ]);
    
    $response = $this->actingAs($user)->get("/akta/{$akta->id_akta}");
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.akta.show');
    $response->assertSee('John Doe');
    $response->assertSee('AJB');
});
