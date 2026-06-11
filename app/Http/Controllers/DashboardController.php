<?php

namespace App\Http\Controllers;

use App\Models\Akta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAkta = Akta::count();

        $aktaDalamProses = Akta::whereIn('status_workflow', ['Draft', 'Diverifikasi'])->count();

        $aktaSelesaiBulanIni = Akta::where('status_workflow', 'Selesai')
            ->whereYear('last_updated', now()->year)
            ->whereMonth('last_updated', now()->month)
            ->count();

        $selesaiAktas = Akta::where('status_workflow', 'Selesai')
            ->select(['tanggal_dibuat', 'last_updated'])
            ->get();

        $averageProcessingTime = 0;
        if ($selesaiAktas->isNotEmpty()) {
            $totalDays = $selesaiAktas->sum(fn ($a) => \Carbon\Carbon::parse($a->tanggal_dibuat)->diffInDays($a->last_updated));
            $averageProcessingTime = round($totalDays / $selesaiAktas->count(), 1);
        }

        $chartData = Akta::where('tanggal_dibuat', '>=', now()->subMonths(12))
            ->get()
            ->groupBy(fn ($item) => $item->tanggal_dibuat->format('Y-m'))
            ->map(fn ($items, $key) => [
                'label' => \Carbon\Carbon::parse($key . '-01')->format('M Y'),
                'count' => $items->count(),
            ])
            ->values()
            ->sortBy('label')
            ->values();

        $verificationQueue = Akta::where('status_workflow', 'Diverifikasi')
            ->with(['klien', 'user'])
            ->orderBy('tanggal_dibuat', 'asc')
            ->limit(10)
            ->get();

        $breadcrumbs = [['label' => 'Dashboard']];

        return view('pages.dashboard', compact(
            'totalAkta',
            'aktaDalamProses',
            'aktaSelesaiBulanIni',
            'averageProcessingTime',
            'chartData',
            'verificationQueue',
            'breadcrumbs'
        ));
    }
}
