<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Http\Requests\StoreKlienRequest;
use App\Http\Requests\UpdateKlienRequest;
use Illuminate\Http\Request;

class KlienController extends Controller
{
    public function index(Request $request)
    {
        $query = Klien::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $kliens = $query->orderBy('id_klien', 'desc')->paginate(10)->withQueryString();

        return view('pages.klien.index', compact('kliens'));
    }

    public function create()
    {
        return view('pages.klien.create');
    }

    public function store(StoreKlienRequest $request)
    {
        Klien::create($request->validated());

        return redirect()->route('klien.index')
            ->with('success', 'Data klien berhasil ditambahkan.');
    }

    public function show($id)
    {
        $klien = Klien::with('akta')->findOrFail($id);

        return view('pages.klien.show', compact('klien'));
    }

    public function edit($id)
    {
        $klien = Klien::findOrFail($id);

        return view('pages.klien.edit', compact('klien'));
    }

    public function update(UpdateKlienRequest $request, $id)
    {
        $klien = Klien::findOrFail($id);
        $klien->update($request->validated());

        return redirect()->route('klien.show', $klien->id_klien)
            ->with('success', 'Data klien berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $klien = Klien::findOrFail($id);
        $klien->delete();

        return redirect()->route('klien.index')
            ->with('success', 'Data klien berhasil dihapus.');
    }

    public function import()
    {
        return view('pages.klien.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getPathname()));

        $headers = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (!empty($data['nik']) && !empty($data['nama_lengkap'])) {
                Klien::firstOrCreate(
                    ['nik' => $data['nik']],
                    [
                        'nama_lengkap' => $data['nama_lengkap'] ?? '',
                        'tempat_tanggal_lahir' => $data['tempat_tanggal_lahir'] ?? '',
                        'jenis_kelamin' => $data['jenis_kelamin'] ?? '',
                        'alamat' => $data['alamat'] ?? '',
                        'nomor_telepon' => $data['nomor_telepon'] ?? '',
                        'pekerjaan' => $data['pekerjaan'] ?? '',
                        'npwp' => $data['npwp'] ?? '-',
                    ]
                );
            }
        }

        return redirect()->route('klien.index')
            ->with('success', 'Import berhasil diproses.');
    }
}
