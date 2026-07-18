<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use App\Models\TemplateAkta;
use App\Models\User;
use App\Support\PdfReportGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $query = Akta::with(['klien', 'user']);
        $this->applyFilters($query, $request);

        $laporan = $query->orderBy('tanggal_dibuat', 'desc')->get();
        $stats = $this->computeStats($laporan);

        $filters = array_merge(
            [
                'status' => '',
                'jenis' => '',
                'user' => '',
                'tanggal_mulai' => '',
                'tanggal_akhir' => '',
                'bulan' => '',
                'tahun' => '',
            ],
            $request->only(['status', 'jenis', 'user', 'tanggal_mulai', 'tanggal_akhir', 'bulan', 'tahun'])
        );

        $options = [
            'statuses' => ['Draft', 'Diverifikasi', 'Final', 'Selesai'],
            'jenis' => TemplateAkta::orderBy('title')->pluck('title', 'title')->all(),
            'users' => User::orderBy('nama_lengkap')->pluck('nama_lengkap', 'id_user')->all(),
        ];

        return view('pages.laporan.index', array_merge(
            compact('laporan', 'filters', 'options'),
            [
                'stats' => $stats,
                'totalAkta' => $stats['total'],
                'totalSelesai' => $stats['selesai'],
                'filterQuery' => http_build_query(array_filter($filters, fn ($v) => $v !== '' && $v !== null)),
            ]
        ));
    }

    public function export(Request $request, string $type)
    {
        $query = Akta::with(['klien', 'user']);
        $this->applyFilters($query, $request);

        $laporan = $query->orderBy('tanggal_dibuat', 'desc')->get();
        $stats = $this->computeStats($laporan);

        if ($type === 'excel') {
            return response($this->csvBody($laporan, $stats), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="laporan-akta.csv"',
            ]);
        }

        $pdf = $this->buildPdf($laporan, $stats, $request);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-akta.pdf"',
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
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
                Carbon::parse($request->tanggal_mulai)->startOfDay(),
                Carbon::parse($request->tanggal_akhir)->endOfDay(),
            ]);
        } elseif ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereYear('tanggal_dibuat', $request->tahun)
                ->whereMonth('tanggal_dibuat', $request->bulan);
        }
    }

    /**
     * @param  Collection<int, Akta>  $items
     * @return array<string, mixed>
     */
    private function computeStats(Collection $items): array
    {
        $byStatus = ['Draft' => 0, 'Diverifikasi' => 0, 'Final' => 0, 'Selesai' => 0];

        foreach ($items as $item) {
            if (isset($byStatus[$item->status_workflow])) {
                $byStatus[$item->status_workflow]++;
            }
        }

        $byJenis = [];
        foreach ($items as $item) {
            $key = $item->jenis_template ?: '(tanpa jenis)';
            $byJenis[$key] = ($byJenis[$key] ?? 0) + 1;
        }
        arsort($byJenis);

        $monthly = [];
        foreach ($items as $item) {
            $key = $item->tanggal_dibuat ? $item->tanggal_dibuat->format('Y-m') : '-';
            $monthly[$key] = ($monthly[$key] ?? 0) + 1;
        }
        krsort($monthly);

        return [
            'total' => $items->count(),
            'selesai' => $byStatus['Selesai'],
            'dalam_proses' => $byStatus['Draft'] + $byStatus['Diverifikasi'],
            'final' => $byStatus['Final'],
            'by_status' => $byStatus,
            'by_jenis' => $byJenis,
            'monthly' => $monthly,
        ];
    }

    /**
     * @param  Collection<int, Akta>  $laporan
     */
    private function buildPdf(Collection $laporan, array $stats, Request $request): string
    {
        $gen = new PdfReportGenerator();
        $gen->title('Laporan Akta - SIM Akta Notaris & PPAT');
        $gen->meta([
            'Dicetak: '.now()->format('d/m/Y H:i'),
            $this->filterDescription($request),
        ]);

        $gen->keyValues('Ringkasan', [
            ['Total Akta', $stats['total']],
            ['Selesai', $stats['selesai']],
            ['Dalam Proses (Draft + Diverifikasi)', $stats['dalam_proses']],
            ['Final', $stats['final']],
        ]);

        $gen->keyValues(
            'Statistik per Status',
            array_map(fn ($k, $v) => [$k, $v], array_keys($stats['by_status']), array_values($stats['by_status']))
        );

        if ($stats['by_jenis'] !== []) {
            $gen->keyValues(
                'Statistik per Jenis Akta',
                array_map(fn ($k, $v) => [$k, $v], array_keys($stats['by_jenis']), array_values($stats['by_jenis']))
            );
        }

        if ($stats['monthly'] !== []) {
            $gen->keyValues(
                'Ringkasan Bulanan',
                array_map(fn ($k, $v) => [$k, $v], array_keys($stats['monthly']), array_values($stats['monthly']))
            );
        }

        $rows = [];
        $no = 1;
        foreach ($laporan as $item) {
            $rows[] = [
                $no++,
                $item->jenis_template,
                $item->klien?->nama_lengkap ?? '-',
                $item->status_workflow,
                $item->tanggal_dibuat ? $item->tanggal_dibuat->format('d/m/Y') : '-',
            ];
        }
        $gen->table('Daftar Akta', ['No', 'Jenis', 'Klien', 'Status', 'Tanggal'], $rows);

        return $gen->render();
    }

    /**
     * @param  Collection<int, Akta>  $laporan
     */
    private function csvBody(Collection $laporan, array $stats): string
    {
        $lines = [];
        $lines[] = 'SIM Akta Notaris & PPAT - Laporan Akta';
        $lines[] = 'Dicetak: '.now()->format('d/m/Y H:i');
        $lines[] = '';
        $lines[] = 'Ringkasan';
        $lines[] = 'Total Akta,'.$stats['total'];
        $lines[] = 'Selesai,'.$stats['selesai'];
        $lines[] = 'Dalam Proses,'.$stats['dalam_proses'];
        $lines[] = 'Final,'.$stats['final'];
        $lines[] = '';
        $lines[] = 'Statistik per Jenis Akta';
        foreach ($stats['by_jenis'] as $key => $count) {
            $lines[] = $this->csvCell($key).','.$count;
        }
        $lines[] = '';
        $lines[] = 'No,Jenis,Klien,Status,Tanggal';
        $no = 1;
        foreach ($laporan as $item) {
            $lines[] = implode(',', [
                $no++,
                $this->csvCell($item->jenis_template),
                $this->csvCell($item->klien?->nama_lengkap ?? '-'),
                $this->csvCell($item->status_workflow),
                $item->tanggal_dibuat ? $item->tanggal_dibuat->format('d/m/Y') : '-',
            ]);
        }

        return implode("\n", $lines);
    }

    private function csvCell(?string $value): string
    {
        $value = (string) $value;

        if (preg_match('/[",\n]/', $value)) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    private function filterDescription(Request $request): string
    {
        $parts = [];

        if ($request->filled('status')) {
            $parts[] = 'Status: '.$request->status;
        }
        if ($request->filled('jenis')) {
            $parts[] = 'Jenis: '.$request->jenis;
        }
        if ($request->filled('user')) {
            $user = User::find($request->user);
            if ($user) {
                $parts[] = 'Petugas: '.$user->nama_lengkap;
            }
        }
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $parts[] = 'Periode: '.$request->tanggal_mulai.' s/d '.$request->tanggal_akhir;
        } elseif ($request->filled('bulan') && $request->filled('tahun')) {
            $parts[] = 'Periode: bulan '.$request->bulan.'/'.$request->tahun;
        }

        return $parts ? 'Filter: '.implode('; ', $parts) : 'Filter: Semua data';
    }
}
