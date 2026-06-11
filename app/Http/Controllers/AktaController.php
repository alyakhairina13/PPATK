<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use App\Models\Klien;
use App\Models\VersionHistory;
use App\Models\Repertorium;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AktaController extends Controller
{
    public function index(Request $request)
    {
        $query = Akta::with(['klien', 'user']);

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

        return view('pages.akta.index', compact('aktas'));
    }

    public function create()
    {
        $kliens = Klien::orderBy('nama_lengkap')->get();
        $jenisAkta = ['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat'];

        return view('pages.akta.create', compact('kliens', 'jenisAkta'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_klien' => 'required|exists:klien,id_klien',
            'jenis_template' => 'required|in:AJB,Perjanjian,Kuasa,PT,Wasiat',
            'konten_teks_utama' => 'nullable|string',
        ]);

        $now = now();
        $akta = Akta::create([
            'id_klien' => $validated['id_klien'],
            'id_user' => Auth::id(),
            'jenis_template' => $validated['jenis_template'],
            'konten_teks_utama' => $validated['konten_teks_utama'] ?? '',
            'status_workflow' => 'Draft',
            'tanggal_dibuat' => $now,
            'last_updated' => $now,
        ]);

        VersionHistory::create([
            'id_akta' => $akta->id_akta,
            'versi_ke' => '1',
            'backup_konten_teks' => $akta->konten_teks_utama,
            'timestamp_perubahan' => $now,
            'diubah_oleh' => Auth::user()->nama_lengkap,
        ]);

        return redirect()->route('akta.edit', $akta->id_akta)
            ->with('success', 'Draft akta berhasil dibuat.');
    }

    public function show($id)
    {
        $akta = Akta::with(['klien', 'user', 'lampiran', 'versionHistory', 'repertorium'])
            ->findOrFail($id);

        return view('pages.akta.show', compact('akta'));
    }

    public function edit($id)
    {
        $akta = Akta::with(['klien', 'lampiran'])->findOrFail($id);

        if ($akta->status_workflow === 'Selesai') {
            return redirect()->route('akta.show', $akta->id_akta)
                ->with('error', 'Akta dengan status Selesai tidak dapat diedit.');
        }

        $kliens = Klien::orderBy('nama_lengkap')->get();
        $jenisAkta = ['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat'];

        return view('pages.akta.edit', compact('akta', 'kliens', 'jenisAkta'));
    }

    public function update(Request $request, $id)
    {
        $akta = Akta::findOrFail($id);

        if ($akta->status_workflow === 'Selesai') {
            abort(403, 'Akta dengan status Selesai tidak dapat diedit.');
        }

        $validated = $request->validate([
            'id_klien' => 'required|exists:klien,id_klien',
            'jenis_template' => 'required|in:AJB,Perjanjian,Kuasa,PT,Wasiat',
            'konten_teks_utama' => 'nullable|string',
        ]);

        $lastVersion = VersionHistory::where('id_akta', $akta->id_akta)
            ->orderByDesc('versi_ke')
            ->first();

        $newVersion = $lastVersion ? (intval($lastVersion->versi_ke) + 1) : 1;

        VersionHistory::create([
            'id_akta' => $akta->id_akta,
            'versi_ke' => (string) $newVersion,
            'backup_konten_teks' => $validated['konten_teks_utama'] ?? $akta->konten_teks_utama,
            'timestamp_perubahan' => now(),
            'diubah_oleh' => Auth::user()->nama_lengkap,
        ]);

        $akta->update([
            'id_klien' => $validated['id_klien'],
            'jenis_template' => $validated['jenis_template'],
            'konten_teks_utama' => $validated['konten_teks_utama'] ?? $akta->konten_teks_utama,
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
}
