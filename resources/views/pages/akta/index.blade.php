<x-layout>
    <x-slot name="title">Manajemen Akta</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Manajemen Akta</h2>
                    <p class="mt-1 text-sm text-gray-600">Kelola dan pantau seluruh dokumen akta klien.</p>
                </div>
                <a href="{{ route('akta.create') }}" class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">add</span>
                    Buat Draft Akta
                </a>
            </div>
        </div>

        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <form method="GET" action="{{ route('akta.index') }}">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                    <div class="flex flex-1 flex-col gap-3 md:flex-row">
                        <div class="relative w-full md:w-44">
                            <select name="status_workflow" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                @foreach(['Draft', 'Diverifikasi', 'Final', 'Selesai'] as $status)
                                    <option value="{{ $status }}" {{ request('status_workflow') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[18px] text-gray-400">arrow_drop_down</span>
                        </div>

                        <div class="relative w-full md:w-44">
                            <select name="jenis_template" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Jenis</option>
                                @foreach(['AJB', 'Perjanjian', 'Kuasa', 'PT', 'Wasiat'] as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis_template') == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[18px] text-gray-400">arrow_drop_down</span>
                        </div>
                    </div>

                    <div class="relative w-full lg:w-80">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-gray-400">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ID Akta, Klien..." class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 pl-10 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID Akta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Klien Utama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Jenis Akta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Update</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($aktas as $akta)
                        @php
                            $nama = $akta->klien->nama_lengkap ?? '-';
                            $initials = collect(explode(' ', $nama))->take(2)->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->implode('');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">AKT-{{ str_pad($akta->id_akta, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-[10px] font-semibold text-white">
                                        {{ $initials }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $nama }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $akta->jenis_template }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match($akta->status_workflow) {
                                        'Draft' => 'bg-gray-100 text-gray-800',
                                        'Diverifikasi' => 'bg-yellow-100 text-yellow-800',
                                        'Final' => 'bg-blue-100 text-blue-800',
                                        'Selesai' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $akta->status_workflow }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $akta->last_updated->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('akta.show', $akta->id_akta) }}" class="text-blue-600 hover:text-blue-900" title="Lihat">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    @if($akta->status_workflow !== 'Selesai')
                                        <a href="{{ route('akta.edit', $akta->id_akta) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                    @endif
                                    @if(!in_array($akta->status_workflow, ['Final', 'Selesai']))
                                        <x-confirm-modal :action="route('akta.destroy', $akta->id_akta)" message="Yakin ingin menghapus akta ini?" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada akta ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($aktas->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-sm text-gray-600">Menampilkan {{ $aktas->firstItem() }}-{{ $aktas->lastItem() }} dari {{ $aktas->total() }} data</span>
                    <div class="flex gap-1">
                        @if($aktas->onFirstPage())
                            <span class="flex h-7 w-7 items-center justify-center rounded border border-gray-200 text-gray-400">
                                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                            </span>
                        @else
                            <a href="{{ $aktas->previousPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded border border-gray-200 text-gray-600 hover:bg-gray-50">
                                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                            </a>
                        @endif

                        @foreach($aktas->getUrlRange(max(1, $aktas->currentPage() - 1), min($aktas->lastPage(), $aktas->currentPage() + 1)) as $page => $url)
                            @if($page == $aktas->currentPage())
                                <span class="flex h-7 w-7 items-center justify-center rounded bg-blue-600 text-xs font-medium text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex h-7 w-7 items-center justify-center rounded border border-gray-200 text-xs text-gray-700 hover:bg-gray-50">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($aktas->hasMorePages())
                            <a href="{{ $aktas->nextPageUrl() }}" class="flex h-7 w-7 items-center justify-center rounded border border-gray-200 text-gray-600 hover:bg-gray-50">
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </a>
                        @else
                            <span class="flex h-7 w-7 items-center justify-center rounded border border-gray-200 text-gray-400">
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

</x-layout>
