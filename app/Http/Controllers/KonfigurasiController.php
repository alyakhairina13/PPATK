<?php

namespace App\Http\Controllers;

use App\Models\KonfigurasiNomor;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class KonfigurasiController extends Controller
{
    public function index(): View
    {
        $konfigurasi = KonfigurasiNomor::first();
        
        if (!$konfigurasi) {
            $konfigurasi = KonfigurasiNomor::create([
                'pattern' => '{NOMOR}/{TAHUN}/{BULAN}-Rptm',
                'reset_period' => 'tahunan',
                'starting_number' => 1,
            ]);
        }

        return view('pages.konfigurasi.index', compact('konfigurasi'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:255',
            'reset_period' => 'required|in:tahunan,bulanan',
            'starting_number' => 'required|integer|min:1',
        ]);

        $konfigurasi = KonfigurasiNomor::first();
        
        if ($konfigurasi) {
            $konfigurasi->update($validated);
        } else {
            KonfigurasiNomor::create($validated);
        }

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Konfigurasi berhasil disimpan');
    }
}
