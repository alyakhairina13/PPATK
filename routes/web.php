<?php

use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RepertoriumController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KlienController;
use App\Http\Controllers\AktaController;
use App\Http\Controllers\LampiranController;
use App\Http\Controllers\TemplateAktaController;

// Redirect root based on role
Route::get('/', function () {
    return redirect()->route(auth()->check() && auth()->user()->isAdminStaff() ? 'akta.index' : 'dashboard');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard (Notaris only)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:Notaris')
        ->name('dashboard');

    // Klien routes
    Route::get('/klien', [KlienController::class, 'index'])->name('klien.index');
    Route::get('/klien/create', [KlienController::class, 'create'])->name('klien.create');
    Route::post('/klien', [KlienController::class, 'store'])->name('klien.store');
    Route::get('/klien/import', [KlienController::class, 'import'])->name('klien.import');
    Route::post('/klien/import', [KlienController::class, 'processImport'])->name('klien.processImport');
    Route::get('/klien/{klien}', [KlienController::class, 'show'])->name('klien.show');
    Route::get('/klien/{klien}/edit', [KlienController::class, 'edit'])->name('klien.edit');
    Route::put('/klien/{klien}', [KlienController::class, 'update'])->name('klien.update');
    Route::delete('/klien/{klien}', [KlienController::class, 'destroy'])->name('klien.destroy');

    // Akta routes
    Route::resource('akta', AktaController::class);
    Route::get('akta/templates/manage', [TemplateAktaController::class, 'index'])
        ->name('akta.templates.index');
    Route::post('akta/templates/manage', [TemplateAktaController::class, 'store'])
        ->name('akta.templates.store');
    Route::delete('akta/templates/manage/{templateAkta}', [TemplateAktaController::class, 'destroy'])
        ->name('akta.templates.destroy');
    Route::put('akta/templates/manage/aliases', [TemplateAktaController::class, 'updateAliases'])
        ->name('akta.templates.aliases.update');
    Route::get('akta/{id}/download', [AktaController::class, 'download'])
        ->name('akta.download');

    // Workflow status routes
    Route::post('akta/{id}/submit-verification', [AktaController::class, 'submitVerification'])
        ->name('akta.submit-verification');
    Route::post('akta/{id}/revert-draft', [AktaController::class, 'revertToDraft'])
        ->name('akta.revert-draft');
    Route::post('akta/{id}/set-final', [AktaController::class, 'setFinal'])
        ->name('akta.set-final');
    Route::post('akta/{id}/set-selesai', [AktaController::class, 'setSelesai'])
        ->name('akta.set-selesai');

    // Lampiran (attachment) routes
    Route::post('akta/{aktaId}/lampiran', [LampiranController::class, 'store'])
        ->name('akta.lampiran.store');
    Route::delete('akta/{aktaId}/lampiran/{dokumenId}', [LampiranController::class, 'destroy'])
        ->name('akta.lampiran.destroy');

    // Repertorium routes
    Route::get('/repertorium', [RepertoriumController::class, 'index'])->name('repertorium.index');
    Route::get('/repertorium/{id}', [RepertoriumController::class, 'show'])->name('repertorium.show');

    // Laporan routes (Notaris only)
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('role:Notaris')
        ->name('laporan.index');
    Route::get('/laporan/export/{type}', [LaporanController::class, 'export'])
        ->middleware('role:Notaris')
        ->name('laporan.export');

    // Konfigurasi routes (Notaris only)
    Route::get('/konfigurasi', [KonfigurasiController::class, 'index'])
        ->middleware('role:Notaris')
        ->name('konfigurasi.index');
    Route::put('/konfigurasi', [KonfigurasiController::class, 'update'])
        ->middleware('role:Notaris')
        ->name('konfigurasi.update');
});
