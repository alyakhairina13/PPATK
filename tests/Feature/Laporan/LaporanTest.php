<?php

use App\Models\User;
use App\Models\Akta;
use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('laporan page is accessible', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/laporan');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.laporan.index');
});

test('can generate report with filters', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(5)->create([
        'status_workflow' => 'Selesai',
        'created_at' => now()->startOfMonth(),
    ]);
    
    Akta::factory()->count(3)->create([
        'status_workflow' => 'Draft',
        'created_at' => now()->startOfMonth(),
    ]);
    
    $response = $this->actingAs($user)->get('/laporan?status=Selesai');
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) {
        return $laporan->where('status_workflow', 'Selesai')->count() === 5;
    });
});

test('can export report', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(10)->create(['status_workflow' => 'Selesai']);
    
    $response = $this->actingAs($user)->get('/laporan/export/pdf?format=pdf');
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

test('can filter report by date range', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(3)->create([
        'tanggal_dibuat' => now()->subDays(10),
        'last_updated' => now()->subDays(10),
    ]);
    
    Akta::factory()->count(5)->create([
        'tanggal_dibuat' => now()->subDays(5),
        'last_updated' => now()->subDays(5),
    ]);
    
    $response = $this->actingAs($user)->get('/laporan?tanggal_mulai=' . now()->subDays(7)->format('Y-m-d') . '&tanggal_akhir=' . now()->format('Y-m-d'));
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) {
        return $laporan->count() === 5;
    });
});

test('can filter report by jenis template', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(3)->create(['jenis_template' => 'AJB']);
    Akta::factory()->count(2)->create(['jenis_template' => 'Perjanjian']);
    
    $response = $this->actingAs($user)->get('/laporan?jenis=AJB');
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) {
        return $laporan->where('jenis_template', 'AJB')->count() === 3;
    });
});

test('can filter report by status workflow', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(4)->create(['status_workflow' => 'Draft']);
    Akta::factory()->count(3)->create(['status_workflow' => 'Diverifikasi']);
    Akta::factory()->count(5)->create(['status_workflow' => 'Final']);
    
    $response = $this->actingAs($user)->get('/laporan?status=Final');
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) {
        return $laporan->where('status_workflow', 'Final')->count() === 5;
    });
});

test('report shows summary statistics', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(10)->create(['status_workflow' => 'Selesai']);
    Akta::factory()->count(5)->create(['status_workflow' => 'Draft']);
    
    $response = $this->actingAs($user)->get('/laporan');
    
    $response->assertStatus(200);
    $response->assertViewHas('totalAkta', 15);
    $response->assertViewHas('totalSelesai', 10);
});

test('can export report as excel', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(10)->create(['status_workflow' => 'Selesai']);
    
    $response = $this->actingAs($user)->get('/laporan/export/excel?format=excel');
    
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('can filter report by user', function () {
    $user1 = User::factory()->create(['nama_lengkap' => 'User One']);
    $user2 = User::factory()->create(['nama_lengkap' => 'User Two']);
    
    Akta::factory()->count(3)->create(['id_user' => $user1->id_user]);
    Akta::factory()->count(2)->create(['id_user' => $user2->id_user]);
    
    $response = $this->actingAs($user1)->get('/laporan?user=' . $user1->id_user);
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) use ($user1) {
        return $laporan->where('id_user', $user1->id_user)->count() === 3;
    });
});

test('report displays klien information', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    
    Akta::factory()->create([
        'id_klien' => $klien->id_klien,
        'status_workflow' => 'Selesai',
    ]);
    
    $response = $this->actingAs($user)->get('/laporan');
    
    $response->assertStatus(200);
    $response->assertSee('John Doe');
});

test('unauthenticated user cannot access laporan', function () {
    $response = $this->get('/laporan');
    
    $response->assertRedirect('/login');
});

test('report can be filtered by month and year', function () {
    $user = User::factory()->create();
    
    Akta::factory()->count(5)->create([
        'tanggal_dibuat' => now()->setMonth(6)->setYear(2026),
        'last_updated' => now()->setMonth(6)->setYear(2026),
    ]);
    
    Akta::factory()->count(3)->create([
        'tanggal_dibuat' => now()->setMonth(5)->setYear(2026),
        'last_updated' => now()->setMonth(5)->setYear(2026),
    ]);
    
    $response = $this->actingAs($user)->get('/laporan?bulan=6&tahun=2026');
    
    $response->assertStatus(200);
    $response->assertViewHas('laporan', function ($laporan) {
        return $laporan->count() === 5;
    });
});
