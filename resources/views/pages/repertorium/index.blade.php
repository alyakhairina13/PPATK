<x-layout>
    <x-slot name="title">Repertorium Digital</x-slot>

    <!-- Page Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4.5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Repertorium Digital</h2>
            <p class="text-xs text-text-muted mt-0.5">Buku daftar kronologis untuk mencatatkan akta-akta yang telah diterbitkan secara resmi.</p>
        </div>
    </div>

    <!-- Data Card Container -->
    <div class="card p-0 overflow-hidden">
        <!-- Filter Search Header -->
        <div class="px-4 py-3 border-b border-black/5 bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-800 tracking-tight mb-2.5">Saring Repertorium</h3>
            <form method="GET" action="{{ route('repertorium.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <div class="flex flex-col gap-1">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nomor Akta</span>
                    <input type="text" name="nomor" value="{{ request('nomor') }}" placeholder="Ketik nomor..." class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container">
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun</span>
                    <input type="number" name="tahun" value="{{ request('tahun') }}" placeholder="Tahun..." class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container">
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Bulan</span>
                    <select name="bulan" class="text-[11px] bg-white border border-black/5 rounded px-2.5 py-1 text-slate-600 font-semibold focus:outline-none focus:ring-1 focus:ring-primary-container cursor-pointer">
                        <option value="">Semua Bulan</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ request('bulan') == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Jenis Naskah</span>
                    <input type="text" name="jenis" value="{{ request('jenis') }}" placeholder="Ketik jenis..." class="text-[11px] px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 text-[11px] bg-slate-900 hover:bg-slate-800 text-white py-1 rounded font-bold shadow-2xs transition-colors">Terapkan</button>
                    <a href="{{ route('repertorium.index') }}" class="flex-1 text-center text-[11px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 py-1 rounded font-bold transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="clean-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">No. Urut</th>
                        <th>Nomor Akta Resmi</th>
                        <th>Sifat / Jenis Akta</th>
                        <th>Nama Penghadap (Klien)</th>
                        <th>Tanggal Terbit</th>
                        <th style="text-align: right; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repertoriums as $rep)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="font-bold text-slate-600">{{ str_pad($rep->indeks_urutan, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="font-mono font-semibold text-slate-800">{{ $rep->nomor_akta_resmi }}</td>
                            <td class="text-slate-500 font-medium">{{ $rep->akta->jenis_template ?? '-' }}</td>
                            <td class="font-semibold text-slate-900">{{ $rep->akta->klien->nama_lengkap ?? '-' }}</td>
                            <td class="text-slate-400 font-medium">{{ $rep->timestamp_generasi->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('repertorium.show', $rep->id_repertorium) }}" class="text-[11px] text-primary-container font-bold hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-xs text-text-muted">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5 mb-2">
                                    <span class="material-symbols-outlined text-[20px]">library_books</span>
                                </div>
                                Tidak ada data repertorium ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($repertoriums->hasPages())
            <div class="px-4 py-3 border-t border-black/5 bg-slate-50/50">
                {{ $repertoriums->links() }}
            </div>
        @endif
    </div>
</x-layout>
