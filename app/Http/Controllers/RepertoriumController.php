<?php

namespace App\Http\Controllers;

use App\Models\Repertorium;
use Illuminate\Http\Request;

class RepertoriumController extends Controller
{
    public function index(Request $request)
    {
        $query = Repertorium::with(['akta', 'akta.klien']);

        if ($request->filled('nomor') || $request->filled('search')) {
            $search = $request->filled('nomor') ? $request->nomor : $request->search;
            $query->where('nomor_akta_resmi', 'like', '%' . $search . '%');
        }

        if ($request->filled('tahun')) {
            $query->where('tahun_buku', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan_buku', $request->bulan);
        }

        if ($request->filled('jenis')) {
            $query->whereHas('akta', fn ($q) => $q->where('jenis_template', 'like', '%' . $request->jenis . '%'));
        }

        $repertoriums = $query->orderBy('indeks_urutan', 'desc')->paginate(15)->withQueryString();

        return view('pages.repertorium.index', compact('repertoriums'));
    }

    public function show($id)
    {
        $repertorium = Repertorium::with(['akta', 'akta.klien', 'akta.user', 'akta.lampiran'])
            ->findOrFail($id);

        return view('pages.repertorium.show', compact('repertorium'));
    }
}
