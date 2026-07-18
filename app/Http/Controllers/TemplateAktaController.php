<?php

namespace App\Http\Controllers;

use App\Models\TemplateAkta;
use App\Services\TagAliasService;
use App\Services\TemplateAktaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateAktaController extends Controller
{
    public function __construct(
        private readonly TemplateAktaService $templateAktaService,
        private readonly TagAliasService $tagAliasService
    ) {
    }

    public function index()
    {
        $templates = TemplateAkta::withCount('akta')->orderBy('title')->get();

        $prefixAliases = $this->tagAliasService->prefixAliases();
        $tagAliases = $this->tagAliasService->tagAliases();

        $detectedPrefixes = [];
        $detectedTags = [];

        $templates->each(function (TemplateAkta $template) use (&$detectedPrefixes, &$detectedTags) {
            $template->setAttribute(
                'grouped_tags',
                TemplateAktaService::groupTagsByPrefix($template->tags ?? [])
            );

            foreach ($template->tags ?? [] as $tag) {
                $detectedTags[$tag] = true;
                $prefix = TemplateAktaService::groupPrefixForTag($tag);

                if ($prefix !== 'lainnya') {
                    $detectedPrefixes[$prefix] = true;
                }
            }
        });

        $prefixKeys = array_unique(array_merge(array_keys($prefixAliases), array_keys($detectedPrefixes)));
        sort($prefixKeys);

        $tagKeys = array_unique(array_merge(array_keys($tagAliases), array_keys($detectedTags)));
        sort($tagKeys);

        return view('pages.akta.templates', compact(
            'templates',
            'prefixAliases',
            'tagAliases',
            'prefixKeys',
            'tagKeys',
        ));
    }

    public function updateAliases(Request $request)
    {
        $validated = $request->validate([
            'prefix_aliases' => ['nullable', 'array'],
            'prefix_aliases.*' => ['nullable', 'string', 'max:150'],
            'tag_aliases' => ['nullable', 'array'],
            'tag_aliases.*' => ['nullable', 'string', 'max:150'],
        ]);

        $this->tagAliasService->update(
            $validated['prefix_aliases'] ?? [],
            $validated['tag_aliases'] ?? [],
        );

        return redirect()
            ->route('akta.templates.index')
            ->with('success', 'Alias prefix dan tag berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150|unique:template_akta,title',
            'template_file' => 'required|file|extensions:doc,docx|max:15360',
        ], [
            'template_file.required' => 'File template wajib dipilih.',
            'template_file.file' => 'Upload template harus berupa file.',
            'template_file.extensions' => 'Format template harus .doc atau .docx. File .docs tidak didukung.',
            'template_file.max' => 'Ukuran file template maksimal 15 MB.',
        ]);

        $file = $request->file('template_file');

        try {
            $tags = $this->templateAktaService->extractTagsFromUploadedFile($file);
            $slug = $this->generateUniqueSlug($validated['title']);
            $filePath = $this->templateAktaService->storeUploadedTemplate($file, $slug);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'template_file' => 'File template tidak dapat diproses: '.$exception->getMessage(),
                ]);
        }

        if ($tags === []) {
            return back()
                ->withInput()
                ->withErrors([
                    'template_file' => 'Tidak ditemukan placeholder seperti {{$nama_field}} di file yang diunggah.',
                ]);
        }

        TemplateAkta::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'original_filename' => $file->getClientOriginalName(),
            'file_extension' => strtolower($file->getClientOriginalExtension()),
            'file_path' => $filePath,
            'tags' => $tags,
        ]);

        return redirect()
            ->route('akta.templates.index')
            ->with('success', 'Template akta berhasil ditambahkan.');
    }

    public function destroy(TemplateAkta $templateAkta)
    {
        if ($templateAkta->akta()->exists()) {
            return redirect()
                ->route('akta.templates.index')
                ->with('error', 'Template tidak dapat dihapus karena sudah dipakai oleh data akta.');
        }

        if ($templateAkta->file_path && Storage::disk('local')->exists($templateAkta->file_path)) {
            Storage::disk('local')->delete($templateAkta->file_path);
        }

        $templateAkta->delete();

        return redirect()
            ->route('akta.templates.index')
            ->with('success', 'Template akta berhasil dihapus.');
    }

    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug(Str::lower($title));
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'template-akta';
        $slug = $baseSlug;
        $counter = 2;

        while (TemplateAkta::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
