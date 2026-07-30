<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use App\Models\Klien;
use App\Models\Repertorium;
use App\Models\TemplateAkta;
use App\Services\PpatConfigurationService;
use App\Services\KlienAutofillService;
use App\Models\VersionHistory;
use App\Services\TemplateAktaService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AktaController extends Controller
{
    public function __construct(
        private readonly TemplateAktaService $templateAktaService,
        private readonly PpatConfigurationService $ppatConfigurationService,
        private readonly KlienAutofillService $klienAutofillService
    ) {
    }

    public function index(Request $request)
    {
        $query = Akta::with(['klien', 'user', 'templateAkta']);

        if ($request->filled('status_workflow')) {
            $query->where('status_workflow', $request->status_workflow);
        }

        if ($request->filled('jenis_template')) {
            $query->where('jenis_template', $request->jenis_template);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id_akta', 'like', "%{$search}%")
                    ->orWhereHas('klien', fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $aktas = $query->orderBy('last_updated', 'desc')->paginate(15);
        $templateTitles = TemplateAkta::orderBy('title')->pluck('title');

        return view('pages.akta.index', compact('aktas', 'templateTitles'));
    }

    public function create()
    {
        $kliens = Klien::orderBy('nama_lengkap')->get();
        $templates = TemplateAkta::orderBy('title')->get();
        $templateOptions = $this->buildTemplateOptions($templates);
        $lockedTemplateValues = $this->ppatConfigurationService->templateDefaults();
        $formValues = $this->sanitizeTemplateFields(old('template_fields', []));
        $klienData = $this->buildKlienData($kliens);
        $klienFieldMap = $this->klienAutofillService->suffixFieldMap();
        $prefixGroupLabels = TemplateAktaService::prefixGroupLabels();
        $tagLabels = TemplateAktaService::tagLabels();

        return view('pages.akta.create', compact(
            'kliens',
            'templates',
            'templateOptions',
            'lockedTemplateValues',
            'formValues',
            'klienData',
            'klienFieldMap',
            'prefixGroupLabels',
            'tagLabels'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_klien' => 'required|exists:klien,id_klien',
            'id_klien_pihak2' => 'nullable|exists:klien,id_klien',
            'template_id' => 'required|exists:template_akta,id_template_akta',
            'template_fields' => 'nullable|array',
        ]);

        $template = TemplateAkta::findOrFail($validated['template_id']);
        $pihak1 = Klien::find($validated['id_klien']);
        $pihak2 = Klien::find($validated['id_klien_pihak2'] ?? null);
        $templateFields = $this->buildTemplateFieldPayload($template, $validated['template_fields'] ?? [], $pihak1, $pihak2);
        $contentJson = json_encode($templateFields, JSON_UNESCAPED_UNICODE) ?: '{}';
        $now = now();

        $akta = Akta::create([
            'id_klien' => $validated['id_klien'],
            'id_klien_pihak2' => $validated['id_klien_pihak2'] ?: null,
            'id_user' => Auth::id(),
            'template_id' => $template->id_template_akta,
            'jenis_template' => $template->title,
            'konten_teks_utama' => $contentJson,
            'status_workflow' => 'Draft',
            'tanggal_dibuat' => $now,
            'last_updated' => $now,
        ]);

        VersionHistory::create([
            'id_akta' => $akta->id_akta,
            'versi_ke' => '1',
            'backup_konten_teks' => $contentJson,
            'timestamp_perubahan' => $now,
            'diubah_oleh' => Auth::user()->nama_lengkap,
        ]);

        return redirect()->route('akta.edit', $akta->id_akta)
            ->with('success', 'Draft akta berhasil dibuat.');
    }

    public function show($id)
    {
        $akta = Akta::with(['klien', 'klienPihak2', 'user', 'lampiran', 'versionHistory', 'repertorium', 'templateAkta'])
            ->findOrFail($id);
        $resolvedContentFields = $this->resolveDisplayContentFields($akta);

        return view('pages.akta.show', compact('akta', 'resolvedContentFields'));
    }

    public function edit($id)
    {
        $akta = Akta::with(['klien', 'klienPihak2', 'lampiran', 'versionHistory', 'templateAkta'])->findOrFail($id);

        if ($akta->status_workflow === 'Selesai') {
            return redirect()->route('akta.show', $akta->id_akta)
                ->with('error', 'Akta dengan status Selesai tidak dapat diedit.');
        }

        $kliens = Klien::orderBy('nama_lengkap')->get();
        $templates = TemplateAkta::orderBy('title')->get();
        $templateOptions = $this->buildTemplateOptions($templates);
        $lockedTemplateValues = $this->ppatConfigurationService->templateDefaults();
        $formValues = old('template_fields')
            ? $this->sanitizeTemplateFields(old('template_fields', []))
            : $this->mergeWithLockedTemplateDefaults($akta->content_fields);
        $klienData = $this->buildKlienData($kliens);
        $klienFieldMap = $this->klienAutofillService->suffixFieldMap();
        $prefixGroupLabels = TemplateAktaService::prefixGroupLabels();
        $tagLabels = TemplateAktaService::tagLabels();

        return view('pages.akta.edit', compact(
            'akta',
            'kliens',
            'templates',
            'templateOptions',
            'lockedTemplateValues',
            'formValues',
            'klienData',
            'klienFieldMap',
            'prefixGroupLabels',
            'tagLabels'
        ));
    }

    public function update(Request $request, $id)
    {
        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow === 'Selesai') {
            abort(403, 'Akta dengan status Selesai tidak dapat diedit.');
        }

        $validated = $request->validate([
            'id_klien' => 'required|exists:klien,id_klien',
            'id_klien_pihak2' => 'nullable|exists:klien,id_klien',
            'template_id' => 'required|exists:template_akta,id_template_akta',
            'template_fields' => 'nullable|array',
        ]);

        $template = TemplateAkta::findOrFail($validated['template_id']);
        $pihak1 = Klien::find($validated['id_klien']);
        $pihak2 = Klien::find($validated['id_klien_pihak2'] ?? null);
        $templateFields = $this->buildTemplateFieldPayload($template, $validated['template_fields'] ?? [], $pihak1, $pihak2);
        $contentJson = json_encode($templateFields, JSON_UNESCAPED_UNICODE) ?: '{}';
        $lastVersion = VersionHistory::where('id_akta', $akta->id_akta)
            ->orderByDesc('versi_ke')
            ->first();

        $newVersion = $lastVersion ? (intval($lastVersion->versi_ke) + 1) : 1;

        VersionHistory::create([
            'id_akta' => $akta->id_akta,
            'versi_ke' => (string) $newVersion,
            'backup_konten_teks' => $contentJson,
            'timestamp_perubahan' => now(),
            'diubah_oleh' => Auth::user()->nama_lengkap,
        ]);

        $akta->update([
            'id_klien' => $validated['id_klien'],
            'id_klien_pihak2' => $validated['id_klien_pihak2'] ?: null,
            'template_id' => $template->id_template_akta,
            'jenis_template' => $template->title,
            'konten_teks_utama' => $contentJson,
            'last_updated' => now(),
        ]);

        return back()->with('success', "Akta berhasil diupdate (v{$newVersion}).");
    }

    public function destroy($id)
    {
        $akta = Akta::findOrFail($id);

        if (in_array($akta->status_workflow, ['Final', 'Selesai'])) {
            abort(403, 'Akta Final/Selesai tidak dapat dihapus.');
        }

        $akta->delete();

        return redirect()->route('akta.index')
            ->with('success', 'Akta berhasil dihapus.');
    }

    public function download($id)
    {
        $akta = Akta::with(['klien', 'klienPihak2', 'templateAkta'])->findOrFail($id);

        if (! $akta->templateAkta) {
            return back()->with('error', 'Template akta belum terhubung pada data ini.');
        }

        try {
            $outputPath = $this->templateAktaService->renderMergedDocument(
                $akta->templateAkta,
                $this->lockedValuesForAkta($akta),
                $akta->id_akta
            );
        } catch (\Throwable $exception) {
            return back()->with('error', 'Dokumen gagal digabungkan: '.$exception->getMessage());
        }

        $extension = $akta->templateAkta->file_extension;
        $downloadName = sprintf(
            'akta-%04d-%s.%s',
            $akta->id_akta,
            $akta->templateAkta->slug,
            $extension
        );

        return response()
            ->download($outputPath, $downloadName, [
                'Content-Type' => $this->templateAktaService->contentTypeForExtension($extension),
            ])
            ->deleteFileAfterSend(true);
    }

    public function submitVerification($id)
    {
        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow !== 'Draft') {
            return back()->with('error', 'Hanya akta Draft yang dapat disubmit.');
        }

        $akta->update(['status_workflow' => 'Diverifikasi', 'last_updated' => now()]);

        return back()->with('success', 'Akta berhasil diajukan untuk verifikasi.');
    }

    public function revertToDraft($id)
    {
        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow !== 'Diverifikasi') {
            return back()->with('error', 'Hanya akta Diverifikasi yang dapat dikembalikan.');
        }

        $akta->update(['status_workflow' => 'Draft', 'last_updated' => now()]);

        return back()->with('success', 'Akta dikembalikan ke Draft.');
    }

    public function setFinal($id)
    {
        $user = Auth::user();
        if ($user->role !== 'Notaris') {
            abort(403, 'Hanya Notaris yang dapat menetapkan Final.');
        }

        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow !== 'Diverifikasi') {
            return back()->with('error', 'Hanya akta Diverifikasi yang dapat ditetapkan Final.');
        }

        $repertorium = $this->generateRepertorium($akta);

        $akta->update(['status_workflow' => 'Final', 'last_updated' => now()]);

        return back()->with('success', "Akta Final. Repertorium: {$repertorium->nomor_akta_resmi}");
    }

    public function setSelesai($id)
    {
        $user = Auth::user();
        if ($user->role !== 'Notaris') {
            abort(403, 'Hanya Notaris yang dapat menetapkan Selesai.');
        }

        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow !== 'Final') {
            return back()->with('error', 'Hanya akta Final yang dapat ditetapkan Selesai.');
        }

        $akta->update(['status_workflow' => 'Selesai', 'last_updated' => now()]);

        return back()->with('success', 'Akta Selesai dan terkunci.');
    }

    private function generateRepertorium(Akta $akta): Repertorium
    {
        $year = date('Y');
        $month = date('m');

        $lastRep = Repertorium::where('tahun_buku', $year)
            ->where('bulan_buku', $month)
            ->orderByDesc('indeks_urutan')
            ->first();

        $nextIndex = $lastRep ? $lastRep->indeks_urutan + 1 : 1;

        $nomorResmi = sprintf('%03d/%s/%s-Repertorium', $nextIndex, $year, $month);

        return Repertorium::create([
            'id_akta' => $akta->id_akta,
            'nomor_akta_resmi' => $nomorResmi,
            'indeks_urutan' => $nextIndex,
            'bulan_buku' => $month,
            'tahun_buku' => $year,
            'timestamp_generasi' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, string>
     */
    private function buildTemplateFieldPayload(TemplateAkta $template, array $fields, ?Klien $pihak1 = null, ?Klien $pihak2 = null): array
    {
        $payload = [];
        $lockedTemplateValues = $this->ppatConfigurationService->templateDefaults();

        foreach ($template->tags ?? [] as $tag) {
            if ($this->ppatConfigurationService->isAutofillTag($tag) && array_key_exists($tag, $lockedTemplateValues)) {
                $payload[$tag] = $lockedTemplateValues[$tag];
                continue;
            }

            if ($this->klienAutofillService->isLockedTag($tag)) {
                $payload[$tag] = $this->klienAutofillService->resolveTagValueForClients($tag, $pihak1, $pihak2);
                continue;
            }

            $payload[$tag] = (string) Arr::get($fields, $tag, '');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, string>
     */
    private function sanitizeTemplateFields(array $fields): array
    {
        $sanitized = [];

        foreach ($fields as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $sanitized[$key] = is_scalar($value) || $value === null
                ? (string) $value
                : '';
        }

        return $sanitized;
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    private function mergeWithLockedTemplateDefaults(array $fields): array
    {
        return array_merge($fields, $this->ppatConfigurationService->templateDefaults());
    }

    /**
     * Resolve the full payload for an akta: stored content merged with the
     * PPAT configuration defaults and the live Pihak 1 / Pihak 2 klien data.
     *
     * @return array<string, string>
     */
    private function lockedValuesForAkta(Akta $akta): array
    {
        $fields = $this->mergeWithLockedTemplateDefaults($akta->content_fields);

        return array_merge(
            $fields,
            $this->klienAutofillService->lockedValuesForTags(
                $akta->templateAkta->tags ?? [],
                $akta->klien,
                $akta->klienPihak2,
            ),
        );
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    private function resolveDisplayContentFields(Akta $akta): array
    {
        $resolved = $this->lockedValuesForAkta($akta);
        $tags = $akta->templateAkta->tags ?? [];

        if (! is_array($tags) || $tags === []) {
            return $resolved;
        }

        $display = [];

        foreach ($tags as $tag) {
            $display[$tag] = $resolved[$tag] ?? '';
        }

        return $display;
    }

    private function buildTemplateOptions($templates): array
    {
        return $templates->mapWithKeys(function ($template) {
            return [
                $template->id_template_akta => [
                    'title' => $template->title,
                    'tags' => $template->tags ?? [],
                ],
            ];
        })->toArray();
    }

    /**
     * Build a client-id keyed map of klien attributes for the front-end
     * auto-fill of Pihak 1 / Pihak 2 fields.
     *
     * @return array<string, array<string, string>>
     */
    private function buildKlienData($kliens): array
    {
        return $kliens->mapWithKeys(function ($klien) {
            return [
                (string) $klien->id_klien => [
                    'nama_lengkap' => (string) $klien->nama_lengkap,
                    'nik' => (string) $klien->nik,
                    'tempat_tanggal_lahir' => (string) $klien->tempat_tanggal_lahir,
                    'jenis_kelamin' => (string) $klien->jenis_kelamin,
                    'alamat' => (string) $klien->alamat,
                    'nomor_telepon' => (string) $klien->nomor_telepon,
                    'pekerjaan' => (string) $klien->pekerjaan,
                    'npwp' => (string) ($klien->npwp ?? ''),
                ],
            ];
        })->toArray();
    }
}
