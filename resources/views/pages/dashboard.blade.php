<x-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-on-surface">Dashboard</h2>
            <p class="text-sm text-text-muted">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="card">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-text-muted">Total Akta</h3>
                    <span class="material-symbols-outlined text-[22px] text-primary">description</span>
                </div>
                <p class="text-2xl font-semibold text-on-surface">{{ number_format($totalAkta) }}</p>
                <p class="mt-1 text-xs text-text-muted">Semua akta terdaftar</p>
            </div>

            <div class="card">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-text-muted">Dalam Proses</h3>
                    <span class="material-symbols-outlined text-[22px] text-yellow-600">pending_actions</span>
                </div>
                <p class="text-2xl font-semibold text-on-surface">{{ number_format($aktaDalamProses) }}</p>
                <p class="mt-1 text-xs text-text-muted">Draft & Diverifikasi</p>
            </div>

            <div class="card">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-text-muted">Selesai Bulan Ini</h3>
                    <span class="material-symbols-outlined text-[22px] text-green-600">task_alt</span>
                </div>
                <p class="text-2xl font-semibold text-on-surface">{{ number_format($aktaSelesaiBulanIni) }}</p>
                <p class="mt-1 text-xs text-text-muted">{{ now()->isoFormat('MMMM YYYY') }}</p>
            </div>

            <div class="card">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-text-muted">Rata-rata Proses</h3>
                    <span class="material-symbols-outlined text-[22px] text-blue-600">query_stats</span>
                </div>
                <p class="text-2xl font-semibold text-on-surface">{{ $averageProcessingTime }}</p>
                <p class="mt-1 text-xs text-text-muted">Hari per akta</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="card lg:col-span-2">
                <h3 class="mb-3 text-base font-semibold">Statistik Akta (12 Bulan Terakhir)</h3>
                <div class="flex h-56 items-center justify-center rounded-md bg-surface-pearl">
                    @if($chartData->count() > 0)
                        <div class="h-full w-full p-3">
                            <canvas id="aktaChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-text-muted">Belum ada data untuk ditampilkan</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-base font-semibold">Antrian Verifikasi</h3>
                <div class="max-h-56 space-y-2.5 overflow-y-auto">
                    @forelse($verificationQueue as $akta)
                        <a href="{{ route('akta.show', $akta->id_akta) }}" class="block rounded-md bg-surface-pearl p-2.5 transition-colors hover:bg-border-hairline">
                            <p class="text-sm font-medium">{{ $akta->jenis_template }}</p>
                            <p class="mt-1 text-xs text-text-muted">Klien: {{ $akta->klien->nama_lengkap ?? 'N/A' }}</p>
                            <p class="mt-1 text-xs text-primary">{{ $akta->tanggal_dibuat->diffForHumans() }}</p>
                        </a>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-sm text-text-muted">Tidak ada akta yang perlu diverifikasi</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="mb-3 text-base font-semibold">Aktivitas Terbaru</h3>
            <div class="space-y-2.5">
                @php
                    $recentActivity = \App\Models\Akta::with(['klien', 'user'])
                        ->orderBy('last_updated', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @forelse($recentActivity as $akta)
                    <div class="flex items-start gap-3 rounded-md p-2.5 transition-colors hover:bg-surface-pearl">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-primary text-sm font-medium text-white">
                            {{ strtoupper(substr($akta->klien->nama_lengkap ?? 'N', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">{{ $akta->jenis_template }} - {{ $akta->klien->nama_lengkap ?? 'N/A' }}</p>
                            <p class="text-xs text-text-muted">Status: <span class="font-medium">{{ $akta->status_workflow }}</span></p>
                        </div>
                        <div class="flex-shrink-0 text-xs text-text-muted">
                            {{ $akta->last_updated->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center">
                        <p class="text-sm text-text-muted">Belum ada aktivitas</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($chartData->count() > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('aktaChart');
            const chartData = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.label),
                    datasets: [{
                        label: 'Jumlah Akta',
                        data: chartData.map(item => item.count),
                        borderColor: 'rgb(0, 78, 159)',
                        backgroundColor: 'rgba(0, 78, 159, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        </script>
    @endif
</x-layout>
