<x-layout>
    <x-slot name="title">Laporan Berkala</x-slot>

    <div class="space-y-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800">Laporan Berkala</h2>
                <p class="mt-1 text-sm text-gray-600">Filter data akta berdasarkan periode, status, jenis, dan petugas. Statistik di bawah mengikuti filter yang aktif.</p>
            </div>

            <div class="px-6 py-6">
                <form method="GET" action="{{ route('laporan.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" value="{{ $filters['tanggal_akhir'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                                <select name="bulan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                    <option value="">Semua</option>
                                    @for($b = 1; $b <= 12; $b++)
                                        <option value="{{ $b }}" {{ (string) $filters['bulan'] === (string) $b ? 'selected' : '' }}>{{ str_pad((string) $b, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                                <input type="number" name="tahun" value="{{ $filters['tahun'] }}" min="2000" max="2100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Semua</option>
                                @foreach($options['statuses'] as $status)
                                    <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Akta</label>
                            <select name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Semua</option>
                                @foreach($options['jenis'] as $jenis)
                                    <option value="{{ $jenis }}" {{ $filters['jenis'] === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Petugas</label>
                            <select name="user" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <option value="">Semua</option>
                                @foreach($options['users'] as $id => $name)
                                    <option value="{{ $id }}" {{ (string) $filters['user'] === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-5 flex items-center gap-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 text-sm font-medium">Tampilkan</button>
                        <a href="{{ route('laporan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 text-sm font-medium">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-3">
                <h3 class="font-semibold text-gray-900">Hasil Laporan</h3>
                <div class="flex gap-2">
                    <a href="{{ route('laporan.export', 'pdf') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-full text-xs font-medium hover:bg-red-100 ring-1 ring-red-200">Export PDF</a>
                    <a href="{{ route('laporan.export', 'excel') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-full text-xs font-medium hover:bg-green-100 ring-1 ring-green-200">Export CSV</a>
                </div>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total Akta</p><p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p></div>
                    <div class="bg-green-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Selesai</p><p class="text-2xl font-bold text-gray-900">{{ $stats['selesai'] }}</p></div>
                    <div class="bg-amber-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Dalam Proses</p><p class="text-2xl font-bold text-gray-900">{{ $stats['dalam_proses'] }}</p></div>
                    <div class="bg-purple-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Final</p><p class="text-2xl font-bold text-gray-900">{{ $stats['final'] }}</p></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Statistik per Status</h4>
                        <ul class="space-y-1.5 text-sm">
                            @foreach($stats['by_status'] as $status => $count)
                                <li class="flex justify-between"><span class="text-gray-600">{{ $status }}</span><span class="font-semibold text-gray-900">{{ $count }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Statistik per Jenis Akta</h4>
                        @forelse($stats['by_jenis'] as $jenis => $count)
                            <div class="mb-2">
                                <div class="flex justify-between text-sm mb-0.5"><span class="text-gray-600">{{ $jenis }}</span><span class="font-semibold text-gray-900">{{ $count }}</span></div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0 }}%"></div></div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada data.</p>
                        @endforelse
                    </div>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">Ringkasan Bulanan</h4>
                        <ul class="space-y-1.5 text-sm">
                            @forelse($stats['monthly'] as $period => $count)
                                <li class="flex justify-between"><span class="text-gray-600">{{ $period }}</span><span class="font-semibold text-gray-900">{{ $count }}</span></li>
                            @empty
                                <p class="text-sm text-gray-500">Tidak ada data.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Petugas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($laporan as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->jenis_template }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->klien->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->user->nama_lengkap ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $item->status_workflow }}</span></td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->tanggal_dibuat?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layout>
