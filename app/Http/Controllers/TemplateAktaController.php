<?php

namespace App\Http\Controllers;

use App\Models\TemplateAkta;
use App\Services\TemplateAktaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateAktaController extends Controller
{
    public function __construct(private readonly TemplateAktaService $templateAktaService)
    {
    }

    public function index()
    {
        $templates = TemplateAkta::withCount('akta')->orderBy('title')->get();

        return view('pages.akta.templates', compact('templates'));
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
