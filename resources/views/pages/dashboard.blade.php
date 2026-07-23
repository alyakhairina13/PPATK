<x-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-4">
        <!-- Header Page Title -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight leading-tight">Dashboard</h2>
                <p class="text-xs text-text-muted mt-0.5">Ringkasan berkas akta dan performa staf hari ini.</p>
            </div>
            <p class="text-xs text-slate-600 bg-white/70 border border-black/5 px-2.5 py-1 rounded-md shadow-2xs font-semibold">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>

        <!-- Statistics Grid (Compact & Sleek) -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1 -->
            <div class="card p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1">Total Akta</h3>
                    <p class="text-xl font-bold text-slate-900 tracking-tight leading-none">{{ number_format($totalAkta) }}</p>
                    <span class="mt-1.5 text-[10px] text-emerald-600 font-semibold flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[11px] font-bold">trending_up</span> Semua akta terdaftar
                    </span>
                </div>
                <div class="w-9 h-9 rounded-lg bg-blue-50/70 border border-blue-100 text-blue-600 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[20px] font-medium">description</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="card p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1">Dalam Proses</h3>
                    <p class="text-xl font-bold text-slate-900 tracking-tight leading-none">{{ number_format($aktaDalamProses) }}</p>
                    <span class="mt-1.5 text-[10px] text-amber-600 font-semibold flex items-center gap-1">
                        Draft & Verifikasi
                    </span>
                </div>
                <div class="w-9 h-9 rounded-lg bg-amber-50/70 border border-amber-100 text-amber-600 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[20px] font-medium">pending_actions</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="card p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1">Selesai Bulan Ini</h3>
                    <p class="text-xl font-bold text-slate-900 tracking-tight leading-none">{{ number_format($aktaSelesaiBulanIni) }}</p>
                    <span class="mt-1.5 text-[10px] text-slate-400 font-semibold">
                        {{ now()->isoFormat('MMMM YYYY') }}
                    </span>
                </div>
                <div class="w-9 h-9 rounded-lg bg-emerald-50/70 border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[20px] font-medium">task_alt</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="card p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-text-muted mb-1">Rata-rata Waktu</h3>
                    <p class="text-xl font-bold text-slate-900 tracking-tight leading-none">{{ $averageProcessingTime }}</p>
                    <span class="mt-1.5 text-[10px] text-sky-600 font-semibold flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[11px] font-bold">bolt</span> Hari per akta
                    </span>
                </div>
                <div class="w-9 h-9 rounded-lg bg-sky-50/70 border border-sky-100 text-sky-600 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[20px] font-medium">query_stats</span>
                </div>
            </div>
        </div>

        <!-- Chart & Target Progress Grid -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Chart Card -->
            <div class="card p-4 rounded-xl lg:col-span-2 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 tracking-tight">Tren Penerbitan Akta</h3>
                        <p class="text-[10px] text-text-muted">Statistik output dokumen akta 12 bulan terakhir</p>
                    </div>
                </div>
                <div class="h-44 w-full relative">
                    @if($chartData->count() > 0)
                        <canvas id="aktaChart"></canvas>
                    @else
                        <div class="flex h-full items-center justify-center text-xs text-text-muted bg-slate-50 rounded-md border border-black/5">
                            Belum ada data untuk ditampilkan
                        </div>
                    @endif
                </div>
            </div>

            <!-- Target Progress Card -->
            <div class="card p-4 rounded-xl flex flex-col justify-between">
                <div class="mb-3">
                    <h3 class="text-xs font-bold text-slate-800 tracking-tight">Pencapaian Target Bulanan</h3>
                    <p class="text-[10px] text-text-muted">Persentase penyelesaian akta bulan ini</p>
                </div>
                
                <div class="flex items-center justify-around py-2">
                    <!-- Circular Progress SVG -->
                    <div class="relative w-24 h-24">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Base Circle -->
                            <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <!-- Progress Circle -->
                            <path class="text-emerald-500 transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 4px rgba(16, 185, 129, 0.2));" stroke-dasharray="92, 100" stroke-width="3.2" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center leading-none">
                            <span class="text-base font-extrabold text-slate-800">92%</span>
                            <span class="text-[8px] text-slate-400 mt-1 uppercase font-bold tracking-wider">Selesai</span>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <div class="text-left">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Target Akta</p>
                            <p class="text-sm font-extrabold text-slate-800">200 Akta</p>
                        </div>
                        <div class="text-left">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Sisa Waktu</p>
                            <p class="text-xs font-bold text-amber-600 bg-amber-50 border border-amber-100/50 px-1.5 py-0.5 rounded flex items-center gap-1 w-max">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> 13 Hari Lagi
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-black/5 pt-3 mt-1 flex justify-between items-center text-[10px]">
                    <span class="text-slate-500">Kinerja Efisiensi Staf:</span>
                    <span class="font-bold text-emerald-600 flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px] font-bold">bolt</span>Sangat Tinggi
                    </span>
                </div>
            </div>
        </div>

        <!-- Verification Queue & Recent Activity Grid -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- Verification Queue Card -->
            <div class="card p-4 rounded-xl flex flex-col">
                <div class="flex items-center justify-between mb-3.5">
                    <h3 class="text-xs font-bold text-slate-800 tracking-tight">Antrian Verifikasi</h3>
                    <span class="text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-100/50">{{ $verificationQueue->count() }} Tertunda</span>
                </div>
                
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @forelse($verificationQueue as $akta)
                        <a href="{{ route('akta.show', $akta->id_akta) }}" class="block p-2 rounded-lg bg-slate-50 border border-black/5 hover:border-slate-300 transition-colors">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="text-[11px] font-bold text-slate-800">{{ $akta->jenis_template }}</span>
                                <span class="text-[9px] text-slate-400 font-medium">{{ $akta->tanggal_dibuat->diffForHumans() }}</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Klien: {{ $akta->klien->nama_lengkap ?? 'N/A' }}</p>
                            <div class="mt-1.5 flex items-center justify-between">
                                <span class="text-[9px] font-bold text-amber-600 flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-amber-500"></span> Menunggu Verifikasi
                                </span>
                                <span class="text-[9px] font-bold text-primary-container hover:underline">Periksa</span>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5">
                                <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                            </div>
                            <p class="mt-2 text-xs text-text-muted">Tidak ada akta yang perlu diverifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="card p-4 rounded-xl flex flex-col">
                <h3 class="text-xs font-bold text-slate-800 tracking-tight mb-3.5">Aktivitas Terbaru</h3>
                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                    @php
                        $recentActivity = \App\Models\Akta::with(['klien', 'user'])
                            ->orderBy('last_updated', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @forelse($recentActivity as $akta)
                        <div class="flex items-center gap-2.5 p-2 rounded-lg bg-slate-50 border border-black/5 hover:border-slate-300 transition-colors">
                            <div class="w-7 h-7 rounded-md bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center text-[10px] font-bold tracking-wider shadow-2xs shrink-0">
                                {{ strtoupper(substr($akta->klien->nama_lengkap ?? 'N', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-bold text-slate-800 truncate">{{ $akta->jenis_template }} - {{ $akta->klien->nama_lengkap ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-400">Status: <span class="font-bold text-slate-600 text-[9px] bg-slate-200/50 border border-black/5 px-1 py-0.2 rounded">{{ $akta->status_workflow }}</span></p>
                            </div>
                            <div class="text-[9px] text-slate-400 font-medium shrink-0">
                                {{ $akta->last_updated->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <p class="text-xs text-text-muted">Belum ada aktivitas</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($chartData->count() > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('aktaChart').getContext('2d');
                const chartData = @json($chartData);

                // Create gradient for lines
                const gradientNotaris = ctx.createLinearGradient(0, 0, 0, 160);
                gradientNotaris.addColorStop(0, 'rgba(37, 99, 235, 0.15)');
                gradientNotaris.addColorStop(1, 'rgba(37, 99, 235, 0)');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.map(item => item.label),
                        datasets: [{
                            label: 'Jumlah Akta',
                            data: chartData.map(item => item.count),
                            borderColor: '#2563eb', // Blue-600
                            borderWidth: 2,
                            backgroundColor: gradientNotaris,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#2563eb',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 3.5,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(15, 23, 42, 0.85)',
                                titleFont: { size: 10, weight: 'semibold' },
                                bodyFont: { size: 10 },
                                padding: 8,
                                cornerRadius: 6,
                                borderColor: 'rgba(255,255,255,0.1)',
                                borderWidth: 1
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 9, family: 'Inter' }, color: '#94a3b8' }
                            },
                            y: {
                                grid: { color: 'rgba(0,0,0,0.03)' },
                                ticks: { font: { size: 9, family: 'Inter' }, color: '#94a3b8', precision: 0 },
                                border: { dash: [4, 4] }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-layout>
