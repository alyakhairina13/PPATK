<x-layout>
    <x-slot name="title">Detail Klien</x-slot>

    <!-- Page Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4.5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Detail Klien: {{ $klien->nama_lengkap }}</h2>
            <p class="text-xs text-text-muted mt-0.5">Informasi profil lengkap dan rekam histori berkas akta klien.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('klien.index') }}" class="text-xs bg-slate-100 hover:bg-slate-200 border border-black/5 px-3 py-1.5 rounded-md font-semibold text-slate-800 transition-colors">Kembali</a>
            <a href="{{ route('klien.edit', $klien->id_klien) }}" class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-md bg-slate-900 text-white hover:bg-slate-800 active:scale-95 transition-all font-semibold shadow-sm">
                <span class="material-symbols-outlined text-[15px]">edit</span> Edit Profil
            </a>
        </div>
    </div>

    <!-- Info Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Profile Details Card (1/3 Column) -->
        <div class="card flex flex-col items-center p-5 text-center">
            <!-- Visual Initial Avatar Tag -->
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-blue-600 to-teal-500 text-white flex items-center justify-center text-xl font-bold shadow-sm mb-3.5 uppercase">
                {{ strtoupper(substr($klien->nama_lengkap, 0, 2)) }}
            </div>
            <h3 class="font-bold text-sm text-slate-900 leading-tight">{{ $klien->nama_lengkap }}</h3>
            <p class="text-xs text-text-muted mt-0.5 font-medium">{{ $klien->pekerjaan }}</p>
            
            <div class="w-full border-t border-black/5 mt-4 pt-3.5 text-left space-y-2 text-xs">
                <div class="flex justify-between pb-1.5 border-b border-black/5">
                    <span class="text-slate-400 font-semibold">NIK</span>
                    <span class="text-slate-800 font-semibold font-mono">{{ $klien->nik }}</span>
                </div>
                <div class="flex justify-between pb-1.5 border-b border-black/5">
                    <span class="text-slate-400 font-semibold">Telepon</span>
                    <span class="text-slate-800 font-semibold">{{ $klien->nomor_telepon }}</span>
                </div>
                <div class="flex justify-between pb-1.5 border-b border-black/5">
                    <span class="text-slate-400 font-semibold">Gender</span>
                    <span class="text-slate-800 font-semibold">{{ $klien->jenis_kelamin }}</span>
                </div>
                <div class="flex justify-between pb-1.5 border-b border-black/5">
                    <span class="text-slate-400 font-semibold">NPWP</span>
                    <span class="text-slate-800 font-semibold">{{ $klien->npwp ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block mb-0.5">Alamat Lengkap</span>
                    <span class="text-slate-800 font-medium leading-relaxed block">{{ $klien->alamat }}</span>
                </div>
            </div>
            
            <!-- Danger Action -->
            <div class="w-full border-t border-black/5 mt-4.5 pt-3 flex justify-center">
                <x-confirm-modal :action="route('klien.destroy', $klien->id_klien)" message="Yakin ingin menghapus klien {{ $klien->nama_lengkap }}? Semua data akta terkait juga akan terpengaruh.">
                    <span class="text-xs text-red-500 font-bold hover:underline cursor-pointer">Hapus Data Klien</span>
                </x-confirm-modal>
            </div>
        </div>

        <!-- Deed Ledger Card (2/3 Column) -->
        <div class="card md:col-span-2 p-4">
            <h3 class="text-xs font-bold text-slate-800 tracking-tight mb-3">Histori Berkas Terkait</h3>
            
            @if($klien->akta && $klien->akta->count() > 0)
                <div class="overflow-x-auto">
                    <table class="clean-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Jenis Berkas Akta</th>
                                <th>Tanggal Berkas</th>
                                <th>Status Workflow</th>
                                <th style="text-align: right; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($klien->akta as $index => $akta)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-semibold text-slate-900">{{ $akta->jenis_template }}</td>
                                    <td class="text-slate-400 font-medium">{{ $akta->tanggal_dibuat->format('d/m/Y') }}</td>
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
                                    <td style="text-align: right;">
                                        <a href="{{ route('akta.show', $akta->id_akta) }}" class="text-[11px] text-primary-container font-bold hover:underline">Buka</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-xs text-text-muted bg-slate-50/50 rounded-lg border border-black/5">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-black/5 mb-2">
                        <span class="material-symbols-outlined text-[20px]">assignment_late</span>
                    </div>
                    Belum ada riwayat dokumen akta terdaftar untuk klien ini.
                </div>
            @endif
        </div>
    </div>
</x-layout>
