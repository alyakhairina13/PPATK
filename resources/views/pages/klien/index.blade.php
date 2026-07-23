<x-layout>
    <x-slot name="title">Data Klien</x-slot>

    <!-- Page Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4.5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Data Klien</h2>
            <p class="text-xs text-text-muted mt-0.5">Manajemen identitas, riwayat, dan verifikasi berkas klien Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('klien.import') }}" class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-white border border-black/5 hover:bg-slate-100 font-semibold shadow-2xs text-slate-800 transition-colors">
                <span class="material-symbols-outlined text-[15px]">upload_file</span> Import Excel
            </a>
            <a href="{{ route('klien.create') }}" class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-slate-900 text-white hover:bg-slate-800 active:scale-95 transition-all font-semibold shadow-sm">
                <span class="material-symbols-outlined text-[15px]">person_add</span> Tambah Klien
            </a>
        </div>
    </div>

    <!-- Data Card Container -->
    <div class="card p-0 overflow-hidden">
        <!-- Filter and Search Header -->
        <div class="px-4 py-3 border-b border-black/5 flex items-center justify-between flex-wrap gap-2.5 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-800 tracking-tight">Daftar Klien Terdaftar</h3>
            <form method="GET" action="{{ route('klien.index') }}" id="searchForm" class="flex items-center gap-2">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2 top-1.5 text-slate-400 text-[15px]">search</span>
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari nama atau NIK..." class="text-[11px] pl-7 pr-2.5 py-1 border border-black/5 bg-white rounded-md focus:outline-none focus:ring-1 focus:ring-primary-container w-48 shadow-2xs">
                </div>
                @if(request('search'))
                    <a href="{{ route('klien.index') }}" class="text-[11px] bg-slate-100 hover:bg-slate-200 border border-black/5 rounded-md px-2.5 py-1 font-semibold transition-colors">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="clean-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Identitas Klien</th>
                        <th>Nomor Kontak</th>
                        <th>Pekerjaan Utama</th>
                        <th style="text-align: right; width: 180px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kliens as $index => $klien)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td>{{ $kliens->firstItem() + $index }}</td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <!-- Visual Initials Tag using Slate to Teal gradients -->
                                    <div class="w-7 h-7 rounded-md bg-gradient-to-tr from-blue-600 to-teal-500 text-white flex items-center justify-center text-[10px] font-bold shadow-2xs uppercase shrink-0">
                                        {{ strtoupper(substr($klien->nama_lengkap, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="leading-tight font-semibold text-slate-900 truncate">{{ $klien->nama_lengkap }}</p>
                                        <p class="text-[9px] text-slate-400 font-medium mt-0.5">NIK: {{ $klien->nik }} &bull; {{ $klien->jenis_kelamin }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-500 font-medium">{{ $klien->nomor_telepon }}</td>
                            <td class="text-slate-600 font-medium">{{ $klien->pekerjaan }}</td>
                            <td style="text-align: right;">
                                <div class="flex items-center justify-end gap-2.5">
                                    <a href="{{ route('klien.show', $klien->id_klien) }}" class="text-[11px] text-primary-container font-bold hover:underline">Detail</a>
                                    <a href="{{ route('klien.edit', $klien->id_klien) }}" class="text-[11px] text-slate-400 hover:text-slate-700 font-bold transition-colors">Edit</a>
                                    <x-confirm-modal :action="route('klien.destroy', $klien->id_klien)" message="Yakin ingin menghapus klien {{ $klien->nama_lengkap }}?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-xs text-text-muted">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5 mb-2">
                                    <span class="material-symbols-outlined text-[20px]">person_off</span>
                                </div>
                                {{ request('search') ? 'Tidak ada data yang sesuai pencarian' : 'Belum ada data klien terdaftar' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($kliens->hasPages())
            <div class="px-4 py-3 border-t border-black/5 bg-slate-50/50">
                {{ $kliens->links() }}
            </div>
        @endif
    </div>

    <script>
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => document.getElementById('searchForm').submit(), 500);
        });
    </script>
</x-layout>
