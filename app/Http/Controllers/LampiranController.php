<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use App\Models\LampiranDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LampiranController extends Controller
{
    public function store(Request $request, $aktaId)
    {
        $akta = Akta::findOrFail($aktaId);

        if ($akta->status_workflow === 'Selesai') {
            return back()->with('error', 'Akta Selesai tidak dapat dilampiri.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("lampiran/akta_{$aktaId}", $filename, 'public');

        LampiranDokumen::create([
            'id_akta' => $aktaId,
            'nama_file' => $file->getClientOriginalName(),
            'format_extension' => strtolower($file->getClientOriginalExtension()),
            'ukuran_berkas' => round($file->getSize() / 1024 / 1024, 2),
            'path_penyimpanan' => $path,
        ]);

        $akta->update(['last_updated' => now()]);

        return back()->with('success', 'Lampiran berhasil diupload.');
    }

    public function destroy($aktaId, $dokumenId)
    {
        $akta = Akta::findOrFail($aktaId);
        $lampiran = LampiranDokumen::where('id_akta', $aktaId)->findOrFail($dokumenId);

        if ($akta->status_workflow === 'Selesai') {
            return back()->with('error', 'Akta Selesai tidak dapat diedit.');
        }

        if (Storage::disk('public')->exists($lampiran->path_penyimpanan)) {
            Storage::disk('public')->delete($lampiran->path_penyimpanan);
        }

        $lampiran->delete();
        $akta->update(['last_updated' => now()]);

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
