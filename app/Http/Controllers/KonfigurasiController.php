<?php

namespace App\Http\Controllers;

use App\Models\KonfigurasiNomor;
use App\Services\PpatConfigurationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class KonfigurasiController extends Controller
{
    public function __construct(private readonly PpatConfigurationService $ppatConfigurationService)
    {
    }

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

        $ppatConfiguration = $this->ppatConfigurationService->getConfiguration();
        $canManagePpatConfiguration = Auth::user()?->role === 'Notaris';

        return view('pages.konfigurasi.index', compact(
            'konfigurasi',
            'ppatConfiguration',
            'canManagePpatConfiguration'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:255',
            'reset_period' => 'required|in:tahunan,bulanan',
            'starting_number' => 'required|integer|min:1',
            'ppat_name' => 'nullable|string|max:150',
            'work_area' => 'nullable|string|max:255',
            'appointment_number' => 'nullable|string|max:150',
            'appointment_date' => 'nullable|string|max:150',
            'office_address' => 'nullable|string|max:255',
        ]);

        $konfigurasi = KonfigurasiNomor::first();
        
        if ($konfigurasi) {
            $konfigurasi->update([
                'pattern' => $validated['pattern'],
                'reset_period' => $validated['reset_period'],
                'starting_number' => $validated['starting_number'],
            ]);
        } else {
            KonfigurasiNomor::create([
                'pattern' => $validated['pattern'],
                'reset_period' => $validated['reset_period'],
                'starting_number' => $validated['starting_number'],
            ]);
        }

        if (Auth::user()?->role === 'Notaris') {
            $this->ppatConfigurationService->updateConfiguration([
                'ppat_name' => (string) ($validated['ppat_name'] ?? ''),
                'work_area' => (string) ($validated['work_area'] ?? ''),
                'appointment_number' => (string) ($validated['appointment_number'] ?? ''),
                'appointment_date' => (string) ($validated['appointment_date'] ?? ''),
                'office_address' => (string) ($validated['office_address'] ?? ''),
            ]);
        }

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Konfigurasi berhasil disimpan');
    }
}
