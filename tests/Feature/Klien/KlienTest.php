<?php

use App\Models\User;
use App\Models\Klien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('klien index page is accessible', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/klien');
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.klien.index');
});

test('can list klien with pagination', function () {
    $user = User::factory()->create();
    
    Klien::factory()->count(25)->create();
    
    $response = $this->actingAs($user)->get('/klien');
    
    $response->assertStatus(200);
    $response->assertViewHas('kliens', function ($kliens) {
        return $kliens->count() <= 10;
    });
});

test('can search klien by name', function () {
    $user = User::factory()->create();
    
    Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    Klien::factory()->create(['nama_lengkap' => 'Jane Smith']);
    Klien::factory()->create(['nama_lengkap' => 'Bob Johnson']);
    
    $response = $this->actingAs($user)->get('/klien?search=John');
    
    $response->assertStatus(200);
    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('can search klien by NIK', function () {
    $user = User::factory()->create();
    
    Klien::factory()->create(['nik' => '1234567890123456', 'nama_lengkap' => 'Test User']);
    Klien::factory()->create(['nik' => '9876543210987654', 'nama_lengkap' => 'Other User']);
    
    $response = $this->actingAs($user)->get('/klien?search=1234567890123456');
    
    $response->assertStatus(200);
    $response->assertSee('Test User');
    $response->assertDontSee('Other User');
});

test('can create new klien with valid data', function () {
    $user = User::factory()->create();
    
    $klienData = [
        'nama_lengkap' => 'John Doe',
        'nik' => '1234567890123456',
        'tempat_tanggal_lahir' => 'Jakarta, 01-01-1990',
        'jenis_kelamin' => 'Laki-laki',
        'alamat' => 'Jl. Test No. 123',
        'nomor_telepon' => '081234567890',
        'pekerjaan' => 'Programmer',
        'npwp' => '12.345.678.9-012.345',
    ];
    
    $response = $this->actingAs($user)->post('/klien', $klienData);
    
    $response->assertRedirect('/klien');
    $this->assertDatabaseHas('klien', [
        'nama_lengkap' => 'John Doe',
        'nik' => '1234567890123456',
    ]);
});

test('cannot create klien with duplicate NIK', function () {
    $user = User::factory()->create();
    
    Klien::factory()->create(['nik' => '1234567890123456']);
    
    $klienData = [
        'nama_lengkap' => 'John Doe',
        'nik' => '1234567890123456',
        'tempat_tanggal_lahir' => 'Jakarta, 01-01-1990',
        'jenis_kelamin' => 'Laki-laki',
        'alamat' => 'Jl. Test No. 123',
        'nomor_telepon' => '081234567890',
        'pekerjaan' => 'Programmer',
    ];
    
    $response = $this->actingAs($user)->post('/klien', $klienData);
    
    $response->assertSessionHasErrors('nik');
});

test('cannot create klien with invalid NIK not 16 digits', function () {
    $user = User::factory()->create();
    
    $klienData = [
        'nama_lengkap' => 'John Doe',
        'nik' => '12345',
        'tempat_tanggal_lahir' => 'Jakarta, 01-01-1990',
        'jenis_kelamin' => 'Laki-laki',
        'alamat' => 'Jl. Test No. 123',
        'nomor_telepon' => '081234567890',
        'pekerjaan' => 'Programmer',
    ];
    
    $response = $this->actingAs($user)->post('/klien', $klienData);
    
    $response->assertSessionHasErrors('nik');
});

test('can view klien detail', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    
    $response = $this->actingAs($user)->get("/klien/{$klien->id_klien}");
    
    $response->assertStatus(200);
    $response->assertViewIs('pages.klien.show');
    $response->assertSee('John Doe');
});

test('can edit klien', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create(['nama_lengkap' => 'John Doe']);
    
    $updatedData = [
        'nama_lengkap' => 'John Doe Updated',
        'nik' => $klien->nik,
        'tempat_tanggal_lahir' => 'Jakarta, 01-01-1990',
        'jenis_kelamin' => 'Laki-laki',
        'alamat' => 'Jl. Updated No. 456',
        'nomor_telepon' => '081234567890',
        'pekerjaan' => 'Senior Programmer',
    ];
    
    $response = $this->actingAs($user)->put("/klien/{$klien->id_klien}", $updatedData);
    
    $response->assertRedirect("/klien/{$klien->id_klien}");
    $this->assertDatabaseHas('klien', [
        'id_klien' => $klien->id_klien,
        'nama_lengkap' => 'John Doe Updated',
        'alamat' => 'Jl. Updated No. 456',
    ]);
});

test('can delete klien', function () {
    $user = User::factory()->create();
    $klien = Klien::factory()->create();
    
    $response = $this->actingAs($user)->delete("/klien/{$klien->id_klien}");
    
    $response->assertRedirect('/klien');
    $this->assertDatabaseMissing('klien', [
        'id_klien' => $klien->id_klien,
    ]);
});

test('validation errors are returned for invalid data', function () {
    $user = User::factory()->create();
    
    $invalidData = [
        'nama_lengkap' => '',
        'nik' => 'invalid',
        'jenis_kelamin' => 'Invalid',
    ];
    
    $response = $this->actingAs($user)->post('/klien', $invalidData);
    
    $response->assertSessionHasErrors(['nama_lengkap', 'nik', 'jenis_kelamin']);
});

test('can import klien from CSV', function () {
    Storage::fake('local');
    
    $user = User::factory()->create();
    
    $csv = "nama_lengkap,nik,tempat_tanggal_lahir,jenis_kelamin,alamat,nomor_telepon,pekerjaan\n";
    $csv .= "John Doe,1234567890123456,Jakarta 01-01-1990,Laki-laki,Jl. Test,081234567890,Programmer\n";
    $csv .= "Jane Smith,9876543210987654,Bandung 02-02-1992,Perempuan,Jl. Test 2,081234567891,Designer";
    
    $file = UploadedFile::fake()->createWithContent('klien.csv', $csv);
    
    $response = $this->actingAs($user)->post('/klien/import', [
        'file' => $file,
    ]);
    
    $response->assertRedirect('/klien');
    $this->assertDatabaseHas('klien', ['nama_lengkap' => 'John Doe']);
    $this->assertDatabaseHas('klien', ['nama_lengkap' => 'Jane Smith']);
});

test('required fields must be filled', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->post('/klien', []);
    
    $response->assertSessionHasErrors([
        'nama_lengkap',
        'nik',
        'tempat_tanggal_lahir',
        'jenis_kelamin',
        'alamat',
    ]);
});
