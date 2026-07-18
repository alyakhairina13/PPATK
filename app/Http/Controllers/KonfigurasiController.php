<?php

namespace App\Http\Controllers;

use App\Models\KonfigurasiNomor;
use App\Models\TemplateAkta;
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

        $ppatTagKeys = [];

        foreach (TemplateAkta::all() as $template) {
            foreach ($template->tags ?? [] as $tag) {
                if ($this->ppatConfigurationService->isAutofillTag($tag)) {
                    $ppatTagKeys[$tag] = true;
                }
            }
        }

        foreach (array_keys($ppatConfiguration) as $key) {
            $ppatTagKeys[$key] = true;
        }

        $ppatTagKeys = array_keys($ppatTagKeys);
        sort($ppatTagKeys);

        $canManagePpatConfiguration = Auth::user()?->role === 'Notaris';

        return view('pages.konfigurasi.index', compact(
            'konfigurasi',
            'ppatConfiguration',
            'ppatTagKeys',
            'canManagePpatConfiguration'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:255',
            'reset_period' => 'required|in:tahunan,bulanan',
            'starting_number' => 'required|integer|min:1',
            'ppat_values' => ['nullable', 'array'],
            'ppat_values.*' => ['nullable', 'string', 'max:255'],
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
            $this->ppatConfigurationService->updateConfiguration($validated['ppat_values'] ?? []);
        }

        return redirect()->route('konfigurasi.index')
            ->with('success', 'Konfigurasi berhasil disimpan');
    }
}
