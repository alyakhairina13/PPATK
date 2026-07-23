<x-layout>
    <x-slot name="title">Laporan Berkala</x-slot>

    <div class="space-y-4">
        <!-- Page Header Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Laporan Berkala</h2>
                <p class="text-xs text-text-muted mt-0.5">Filter data akta berdasarkan periode, status, jenis, dan petugas.</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card p-4 rounded-xl">
            <h3 class="text-xs font-bold text-slate-800 tracking-tight mb-3">Saring Laporan</h3>
            <form method="GET" action="{{ route('laporan.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Mulai</span>
                        <input type="date" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] }}" class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container text-slate-700 font-semibold">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Akhir</span>
                        <input type="date" name="tanggal_akhir" value="{{ $filters['tanggal_akhir'] }}" class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container text-slate-700 font-semibold">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Bulan</span>
                        <select name="bulan" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                            <option value="">Semua Bulan</option>
                            @for($b = 1; $b <= 12; $b++)
                                <option value="{{ $b }}" {{ (string) $filters['bulan'] === (string) $b ? 'selected' : '' }}>{{ str_pad((string) $b, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun</span>
                        <input type="number" name="tahun" value="{{ $filters['tahun'] }}" min="2000" max="2100" class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container text-slate-700 font-semibold">
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Status Workflow</span>
                        <select name="status" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                            <option value="">Semua Status</option>
                            @foreach($options['statuses'] as $status)
                                <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Petugas</span>
                        <select name="user" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                            <option value="">Semua Staf</option>
                            @foreach($options['users'] as $id => $name)
                                <option value="{{ $id }}" {{ (string) $filters['user'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" class="text-[11px] bg-slate-900 hover:bg-slate-800 text-white px-4 py-1.5 rounded font-bold shadow-2xs transition-colors">Tampilkan Data</button>
                    <a href="{{ route('laporan.index') }}" class="text-[11px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 px-4 py-1.5 rounded font-bold transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <!-- Output Report Results -->
        <div class="card p-0 overflow-hidden">
            <!-- Header Result Card -->
            <div class="px-4 py-3 border-b border-black/5 flex flex-wrap justify-between items-center gap-3 bg-slate-50/50">
                <h3 class="text-xs font-bold text-slate-800 tracking-tight">Hasil Rekapitulasi Laporan</h3>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export', 'pdf') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" class="flex items-center gap-1 text-[10px] px-2.5 py-1 bg-white border border-red-200 text-red-600 rounded font-semibold hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-[13px] font-bold">picture_as_pdf</span> Ekspor PDF
                    </a>
                    <a href="{{ route('laporan.export', 'excel') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" class="flex items-center gap-1 text-[10px] px-2.5 py-1 bg-white border border-emerald-200 text-emerald-600 rounded font-semibold hover:bg-emerald-50 transition-colors">
                        <span class="material-symbols-outlined text-[13px] font-bold">table_view</span> Ekspor CSV
                    </a>
                </div>
            </div>

            <div class="p-4 space-y-4.5">
                <!-- Summary Numeric Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="p-3 bg-blue-50/60 border border-blue-100/50 rounded-xl">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Total Akta</p>
                        <p class="text-lg font-extrabold text-slate-800 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50/60 border border-emerald-100/50 rounded-xl">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Status Selesai</p>
                        <p class="text-lg font-extrabold text-slate-800 mt-1">{{ $stats['selesai'] }}</p>
                    </div>
                    <div class="p-3 bg-amber-50/60 border border-amber-100/50 rounded-xl">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Dalam Proses</p>
                        <p class="text-lg font-extrabold text-slate-800 mt-1">{{ $stats['dalam_proses'] }}</p>
                    </div>
                    <!-- Completely Slate-based to avoid any purple hue -->
                    <div class="p-3 bg-slate-50/80 border border-slate-100 rounded-xl">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Tahap Final</p>
                        <p class="text-lg font-extrabold text-slate-800 mt-1">{{ $stats['final'] }}</p>
                    </div>
                </div>

                <!-- Breakdown Info -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Status Breakdown -->
                    <div class="p-3.5 border border-black/5 bg-slate-50/30 rounded-xl">
                        <h4 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider mb-2.5 pb-1 border-b border-black/5">Berdasarkan Status</h4>
                        <ul class="space-y-2 text-xs">
                            @foreach($stats['by_status'] as $status => $count)
                                <li class="flex justify-between items-center">
                                    <span class="text-slate-500 font-medium">{{ $status }}</span>
                                    <span class="font-extrabold text-slate-800">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Template Breakdown -->
                    <div class="p-3.5 border border-black/5 bg-slate-50/30 rounded-xl">
                        <h4 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider mb-2.5 pb-1 border-b border-black/5">Berdasarkan Jenis Akta</h4>
                        @forelse($stats['by_jenis'] as $jenis => $count)
                            <div class="mb-2">
                                <div class="flex justify-between text-xs mb-1 font-medium">
                                    <span class="text-slate-500 truncate mr-2">{{ $jenis }}</span>
                                    <span class="font-extrabold text-slate-800 shrink-0">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1">
                                    <div class="bg-blue-600 h-1 rounded-full" style="width: {{ $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-4 text-center">Belum ada data</p>
                        @endforelse
                    </div>

                    <!-- Monthly Breakdown -->
                    <div class="p-3.5 border border-black/5 bg-slate-50/30 rounded-xl">
                        <h4 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider mb-2.5 pb-1 border-b border-black/5">Ringkasan Output Bulanan</h4>
                        <ul class="space-y-2 text-xs">
                            @forelse($stats['monthly'] as $period => $count)
                                <li class="flex justify-between items-center">
                                    <span class="text-slate-500 font-medium">{{ $period }}</span>
                                    <span class="font-extrabold text-slate-800">{{ $count }}</span>
                                </li>
                            @empty
                                <p class="text-xs text-slate-400 py-4 text-center">Belum ada data</p>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Detailed Table Grid -->
                <div class="overflow-x-auto">
                    <table class="clean-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Jenis Akta / Template</th>
                                <th>Nama Klien</th>
                                <th>Petugas Staf</th>
                                <th>Status Workflow</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan as $index => $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-semibold text-slate-900">{{ $item->jenis_template }}</td>
                                    <td class="font-semibold text-slate-700">{{ $item->klien->nama_lengkap ?? '-' }}</td>
                                    <td class="text-slate-500 font-medium">{{ $item->user->nama_lengkap ?? '-' }}</td>
                                    <td>
                                        <span class="badge 
                                            {{ match($item->status_workflow) {
                                                'Draft' => 'badge-info',
                                                'Diverifikasi' => 'badge-warning',
                                                'Final' => 'badge-success',
                                                'Selesai' => 'badge-success',
                                                default => 'badge-info',
                                            } }}">
                                            <span class="w-1.5 h-1.5 rounded-full 
                                                {{ match($item->status_workflow) {
                                                    'Draft' => 'bg-blue-500',
                                                    'Diverifikasi' => 'bg-amber-500',
                                                    'Final' => 'bg-emerald-500',
                                                    'Selesai' => 'bg-emerald-500',
                                                    default => 'bg-blue-500',
                                                } }}"></span>
                                            {{ strtoupper($item->status_workflow) }}
                                        </span>
                                    </td>
                                    <td class="text-slate-400 font-medium">{{ $item->tanggal_dibuat?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-xs text-text-muted">
                                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5 mb-2">
                                            <span class="material-symbols-outlined text-[20px]">assignment_late</span>
                                        </div>
                                        Tidak ada akta ditemukan untuk filter ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout>
