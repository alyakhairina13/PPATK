<?php

use App\Models\User;
use App\Models\Akta;
use App\Models\Klien;
use App\Models\Repertorium;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('repertorium index page is accessible', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/repertorium');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.repertorium.index');
});

test('can list repertorium', function () {
    $user = User::factory()->create();
    
    $akta1 = Akta::factory()->final()->create();
    $akta2 = Akta::factory()->final()->create();
    
    Repertorium::factory()->create([
        'id_akta' => $akta1->id_akta,
        'nomor_akta_resmi' => 'AKT-2026-0001',
    ]);
    Repertorium::factory()->create([
        'id_akta' => $akta2->id_akta,
        'nomor_akta_resmi' => 'AKT-2026-0002',
    ]);
    
    $response = $this->actingAs($user)->get('/repertorium');
    
    $response->assertStatus(200);
    $response->assertSee('AKT-2026-0001');
    $response->assertSee('AKT-2026-0002');
});

test('can search repertorium by nomor', function () {
    $user = User::factory()->create();
    
    $akta1 = Akta::factory()->final()->create();
    $akta2 = Akta::factory()->final()->create();
    
    Repertorium::factory()->create([
        'id_akta' => $akta1->id_akta,
        'nomor_akta_resmi' => 'AKT-2026-0001',
    ]);
    Repertorium::factory()->create([
        'id_akta' => $akta2->id_akta,
        'nomor_akta_resmi' => 'AKT-2026-0002',
    ]);
    
    $response = $this->actingAs($user)->get('/repertorium?nomor=0001');
    
    $response->assertStatus(200);
    $response->assertSee('AKT-2026-0001');
    $response->assertDontSee('AKT-2026-0002');
});

test('can view repertorium detail', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    $akta = Akta::factory()->final()->create([
        'id_klien' => $klien->id_klien,
        'jenis_template' => 'AJB',
    ]);
    $repertorium = Repertorium::factory()->create([
        'id_akta' => $akta->id_akta,
        'nomor_akta_resmi' => 'AKT-2026-0001',
    ]);
    
    $response = $this->actingAs($user)->get("/repertorium/{$repertorium->id_repertorium}");
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.repertorium.show');
    $response->assertSee('AKT-2026-0001');
    $response->assertSee('John Doe');
    $response->assertSee('AJB');
});

test('repertorium displays correct month and year', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->final()->create();
    
    $repertorium = Repertorium::factory()->create([
        'id_akta' => $akta->id_akta,
        'bulan_buku' => 6,
        'tahun_buku' => 2026,
    ]);
    
    $response = $this->actingAs($user)->get("/repertorium/{$repertorium->id_repertorium}");
    
    $response->assertStatus(200);
    $response->assertSee('2026');
});

test('can filter repertorium by year', function () {
    $user = User::factory()->create();
    
    $akta1 = Akta::factory()->final()->create();
    $akta2 = Akta::factory()->final()->create();
    
    Repertorium::factory()->create([
        'id_akta' => $akta1->id_akta,
        'tahun_buku' => 2026,
        'nomor_akta_resmi' => 'AKT-2026-0001',
    ]);
    Repertorium::factory()->create([
        'id_akta' => $akta2->id_akta,
        'tahun_buku' => 2025,
        'nomor_akta_resmi' => 'AKT-2025-0001',
    ]);
    
    $response = $this->actingAs($user)->get('/repertorium?tahun=2026');
    
    $response->assertStatus(200);
    $response->assertSee('AKT-2026-0001');
    $response->assertDontSee('AKT-2025-0001');
});

test('can filter repertorium by month', function () {
    $user = User::factory()->create();
    
    $akta1 = Akta::factory()->final()->create();
    $akta2 = Akta::factory()->final()->create();
    
    Repertorium::factory()->create([
        'id_akta' => $akta1->id_akta,
        'bulan_buku' => 6,
        'nomor_akta_resmi' => 'AKT-2026-0001',
    ]);
    Repertorium::factory()->create([
        'id_akta' => $akta2->id_akta,
        'bulan_buku' => 5,
        'nomor_akta_resmi' => 'AKT-2026-0002',
    ]);
    
    $response = $this->actingAs($user)->get('/repertorium?bulan=6');
    
    $response->assertStatus(200);
    $response->assertSee('AKT-2026-0001');
    $response->assertDontSee('AKT-2026-0002');
});

test('repertorium list is paginated', function () {
    $user = User::factory()->create();
    
    $aktas = Akta::factory()->count(25)->final()->create();
    
    foreach ($aktas as $akta) {
        Repertorium::factory()->create(['id_akta' => $akta->id_akta]);
    }
    
    $response = $this->actingAs($user)->get('/repertorium');
    
    $response->assertStatus(200);
    $response->assertViewHas('repertoriums', function ($repertoriums) {
        return $repertoriums->count() <= 15;
    });
});

test('repertorium shows indeks urutan', function () {
    $user = User::factory()->create();
    $akta = Akta::factory()->final()->create();
    
    $repertorium = Repertorium::factory()->create([
        'id_akta' => $akta->id_akta,
        'indeks_urutan' => 42,
    ]);
    
    $response = $this->actingAs($user)->get("/repertorium/{$repertorium->id_repertorium}");
    
    $response->assertStatus(200);
    $response->assertSee('42');
});

test('unauthenticated user cannot access repertorium', function () {
    $response = $this->get('/repertorium');
    
    $response->assertRedirect('/login');
});
