<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login page is accessible', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('can login with valid credentials as admin', function () {
    $user = User::factory()->adminStaff()->create([
        'username' => 'admintest',
        'password' => bcrypt('password123'),
    ]);
    
    $response = $this->post('/login', [
        'username' => 'admintest',
        'password' => 'password123',
    ]);
    
    $response->assertRedirect('/akta');
    $this->assertAuthenticatedAs($user);
});

test('can login with valid credentials as notaris', function () {
    $user = User::factory()->notaris()->create([
        'username' => 'notaristest',
        'password' => bcrypt('password123'),
    ]);
    
    $response = $this->post('/login', [
        'username' => 'notaristest',
        'password' => 'password123',
    ]);
    
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('cannot login with invalid password', function () {
    User::factory()->create([
        'username' => 'testuser',
        'password' => bcrypt('correctpassword'),
    ]);
    
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'wrongpassword',
    ]);
    
    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('cannot login with non-existent username', function () {
    $response = $this->post('/login', [
        'username' => 'nonexistent',
        'password' => 'password123',
    ]);
    
    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('can logout', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);
    
    $response = $this->post('/logout');
    
    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get('/dashboard');
    
    $response->assertRedirect('/login');
});

test('login with remember me functionality', function () {
    $user = User::factory()->notaris()->create([
        'username' => 'testuser',
        'password' => bcrypt('password123'),
    ]);
    
    $response = $this->post('/login', [
        'username' => 'testuser',
        'password' => 'password123',
        'remember' => true,
    ]);
    
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});
