<x-layout>
    <x-slot name="title">Manajemen Akta</x-slot>

    <!-- Page Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4.5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Manajemen Akta</h2>
            <p class="text-xs text-text-muted mt-0.5">Kelola pembuatan akta, naskah template, dan lacak status tahapan workflow berkas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('akta.templates.index') }}" class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-white border border-black/5 hover:bg-slate-100 font-semibold shadow-2xs text-slate-800 transition-colors">
                <span class="material-symbols-outlined text-[15px]">library_books</span> Template Akta
            </a>
            <a href="{{ route('akta.create') }}" class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-slate-900 text-white hover:bg-slate-800 active:scale-95 transition-all font-semibold shadow-sm">
                <span class="material-symbols-outlined text-[15px]">add</span> Buat Draft Akta
            </a>
        </div>
    </div>

    <!-- Data Card Container -->
    <div class="card p-0 overflow-hidden">
        <!-- Filter and Search Header -->
        <div class="px-4 py-3 border-b border-black/5 flex items-center justify-between flex-wrap gap-2.5 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-800 tracking-tight">Berkas Akta</h3>
            <form method="GET" action="{{ route('akta.index') }}" class="flex items-center gap-2 flex-wrap">
                <!-- Status Workflow Filter -->
                <select name="status_workflow" onchange="this.form.submit()" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                    <option value="">Semua Status</option>
                    @foreach(['Draft', 'Diverifikasi', 'Final', 'Selesai'] as $status)
                        <option value="{{ $status }}" {{ request('status_workflow') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>

                <!-- Jenis Template Filter -->
                <select name="jenis_template" onchange="this.form.submit()" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                    <option value="">Semua Jenis</option>
                    @foreach($templateTitles as $jenis)
                        <option value="{{ $jenis }}" {{ request('jenis_template') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>

                <!-- Search Field -->
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2 top-1.5 text-slate-400 text-[15px]">search</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari akta atau klien..." class="text-[11px] pl-7 pr-2.5 py-1 border border-black/5 bg-white rounded-md focus:outline-none focus:ring-1 focus:ring-primary-container w-44 shadow-2xs">
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="clean-table">
                <thead>
                    <tr>
                        <th>Nomor Akta</th>
                        <th>Klien Utama</th>
                        <th>Jenis Akta</th>
                        <th>Status Workflow</th>
                        <th>Last Update</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aktas as $akta)
                        @php
                            $nama = $akta->klien->nama_lengkap ?? '-';
                            $initials = collect(explode(' ', $nama))->take(2)->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="font-mono text-[11px] text-slate-600">AKT-{{ str_pad($akta->id_akta, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <!-- Initials Tag -->
                                    <div class="w-6 h-6 rounded bg-gradient-to-tr from-blue-600 to-teal-500 text-white flex items-center justify-center text-[9px] font-bold shadow-2xs uppercase">
                                        {{ $initials }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $nama }}</span>
                                </div>
                            </td>
                            <td class="text-slate-500 font-medium">{{ $akta->jenis_template }}</td>
                            <td>
                                <span class="badge 
                                    {{ match($akta->status_workflow) {
                                        'Draft' => 'badge-info',
                                        'Diverifikasi' => 'badge-warning',
                                        'Final' => 'badge-success',
                                        'Selesai' => 'badge-success',
                                        default => 'badge-info',
                                    } }}">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                        {{ match($akta->status_workflow) {
                                            'Draft' => 'bg-blue-500',
                                            'Diverifikasi' => 'bg-amber-500',
                                            'Final' => 'bg-emerald-500',
                                            'Selesai' => 'bg-emerald-500',
                                            default => 'bg-blue-500',
                                        } }}"></span>
                                    {{ strtoupper($akta->status_workflow) }}
                                </span>
                            </td>
                            <td class="text-slate-400 font-medium">{{ $akta->last_updated->translatedFormat('d M Y') }}</td>
                            <td style="text-align: right;">
                                <div class="flex items-center justify-end gap-2.5">
                                    <a href="{{ route('akta.show', $akta->id_akta) }}" class="text-[11px] text-primary-container font-bold hover:underline">Kelola</a>
                                    
                                    @if($akta->templateAkta)
                                        <a href="{{ route('akta.download', $akta->id_akta) }}" class="text-[11px] text-emerald-600 font-bold hover:underline">Unduh</a>
                                    @endif
                                    
                                    @if($akta->status_workflow !== 'Selesai')
                                        <a href="{{ route('akta.edit', $akta->id_akta) }}" class="text-[11px] text-slate-400 hover:text-slate-700 font-bold transition-colors">Edit</a>
                                    @endif

                                    @if(!in_array($akta->status_workflow, ['Final', 'Selesai']))
                                        <x-confirm-modal :action="route('akta.destroy', $akta->id_akta)" message="Yakin ingin menghapus akta ini?" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-xs text-text-muted">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5 mb-2">
                                    <span class="material-symbols-outlined text-[20px]">assignment_late</span>
                                </div>
                                Tidak ada akta ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($aktas->hasPages())
            <div class="px-4 py-3 border-t border-black/5 bg-slate-50/50 flex items-center justify-between flex-wrap gap-2 text-xs text-slate-500">
                <span>Menampilkan {{ $aktas->firstItem() }}-{{ $aktas->lastItem() }} dari {{ $aktas->total() }} akta</span>
                <div class="flex gap-1">
                    @if($aktas->onFirstPage())
                        <span class="flex h-6 w-6 items-center justify-center rounded border border-black/5 text-slate-300 bg-white">
                            <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $aktas->previousPageUrl() }}" class="flex h-6 w-6 items-center justify-center rounded border border-black/5 text-slate-600 bg-white hover:bg-slate-50">
                            <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                        </a>
                    @endif

                    @foreach($aktas->getUrlRange(max(1, $aktas->currentPage() - 1), min($aktas->lastPage(), $aktas->currentPage() + 1)) as $page => $url)
                        @if($page == $aktas->currentPage())
                            <span class="flex h-6 w-6 items-center justify-center rounded bg-slate-900 text-xs font-bold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex h-6 w-6 items-center justify-center rounded border border-black/5 text-xs text-slate-600 bg-white hover:bg-slate-50">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($aktas->hasMorePages())
                        <a href="{{ $aktas->nextPageUrl() }}" class="flex h-6 w-6 items-center justify-center rounded border border-black/5 text-slate-600 bg-white hover:bg-slate-50">
                            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        </a>
                    @else
                        <span class="flex h-6 w-6 items-center justify-center rounded border border-black/5 text-slate-300 bg-white">
                            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-layout>
