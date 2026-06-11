<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use App\Models\Klien;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Akta::with('klien');

        if ($request->filled('status')) {
            $query->where('status_workflow', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_template', $request->jenis);
        }

        if ($request->filled('user')) {
            $query->where('id_user', $request->user);
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_dibuat', [
                $request->tanggal_mulai,
                $request->tanggal_akhir,
            ]);
        } elseif ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereYear('tanggal_dibuat', $request->tahun)
                ->whereMonth('tanggal_dibuat', $request->bulan);
        }

        $laporan = $query->orderBy('tanggal_dibuat', 'desc')->get();

        $totalAkta = Akta::count();
        $totalSelesai = Akta::where('status_workflow', 'Selesai')->count();

        return view('pages.laporan.index', compact('laporan', 'totalAkta', 'totalSelesai'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:akta,klien',
            'period_type' => 'required|in:harian,mingguan,bulanan,tahunan',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        if ($validated['report_type'] === 'akta') {
            $data = Akta::with('klien')
                ->whereBetween('tanggal_dibuat', [$startDate, $endDate])
                ->orderBy('tanggal_dibuat', 'desc')
                ->get();

            $summary = [
                'total' => $data->count(),
                'selesai' => $data->where('status_workflow', 'Selesai')->count(),
                'by_jenis' => $data->groupBy('jenis_template')->map->count(),
            ];
        } else {
            $data = Klien::withCount('akta')
                ->orderBy('id_klien', 'desc')
                ->get();

            $summary = [
                'total' => $data->count(),
                'total_aktas' => $data->sum('akta_count'),
            ];
        }

        return view('pages.laporan.index', [
            'data' => $data,
            'summary' => $summary,
            'filters' => $validated,
        ]);
    }

    public function export(Request $request, string $type)
    {
        $format = $request->query('format', 'pdf');

        $data = Akta::with('klien')
            ->where('status_workflow', 'Selesai')
            ->orderBy('tanggal_dibuat', 'desc')
            ->get();

        if ($format === 'excel') {
            return response('', 200)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        return response('', 200)
            ->header('Content-Type', 'application/pdf');
    }
}
