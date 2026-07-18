<?php

use App\Models\User;
use App\Models\Akta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard is accessible when authenticated', function () {
    $user = User::factory()->notaris()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.dashboard');
});

test('dashboard shows correct stats', function () {
    $user = User::factory()->notaris()->create();
    
    Akta::factory()->count(5)->create(['status_workflow' => 'Draft']);
    Akta::factory()->count(3)->create(['status_workflow' => 'Diverifikasi']);
    Akta::factory()->count(2)->create(['status_workflow' => 'Final']);
    Akta::factory()->count(4)->create([
        'status_workflow' => 'Selesai',
        'created_at' => now(),
    ]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertViewHas('totalAkta', 14);
    $response->assertViewHas('aktaDalamProses', 8);
    $response->assertViewHas('aktaSelesaiBulanIni', 4);
});

test('unauthenticated user cannot access dashboard', function () {
    $response = $this->get('/dashboard');
    
    $response->assertRedirect('/login');
});

test('dashboard displays user information', function () {
    $user = User::factory()->notaris()->create([
        'nama_lengkap' => 'Test User',
    ]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertSee('Test User');
    $response->assertSee('Notaris');
});

test('dashboard shows verification queue', function () {
    $user = User::factory()->notaris()->create();
    
    Akta::factory()->count(3)->create(['status_workflow' => 'Diverifikasi']);
    Akta::factory()->count(2)->create(['status_workflow' => 'Draft']);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertViewHas('verificationQueue', function ($akta) {
        return $akta->count() === 3;
    });
});

test('admin staff cannot access dashboard', function () {
    $user = User::factory()->adminStaff()->create();
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertRedirect('/akta');
});

test('dashboard shows monthly statistics', function () {
    $user = User::factory()->notaris()->create();
    
    Akta::factory()->count(5)->create([
        'created_at' => now()->startOfMonth(),
        'last_updated' => now()->startOfMonth(),
        'status_workflow' => 'Selesai',
    ]);
    
    Akta::factory()->count(3)->create([
        'created_at' => now()->subMonth(),
        'last_updated' => now()->subMonth(),
        'status_workflow' => 'Selesai',
    ]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertViewHas('aktaSelesaiBulanIni', 5);
});
