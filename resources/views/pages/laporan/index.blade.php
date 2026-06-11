<x-layout>
    <x-slot name="title">Laporan Berkala</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800">Laporan Berkala</h2>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('laporan.generate') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Laporan</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2"><input type="radio" name="report_type" value="akta" {{ old('report_type', $filters['report_type'] ?? 'akta') == 'akta' ? 'checked' : '' }} class="text-blue-600"> Laporan Akta</label>
                            <label class="flex items-center gap-2"><input type="radio" name="report_type" value="klien" {{ old('report_type', $filters['report_type'] ?? '') == 'klien' ? 'checked' : '' }} class="text-blue-600"> Klien Baru</label>
                        </div>
                        @error('report_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'tahunan' => 'Tahunan'] as $val => $label)
                                <label class="flex items-center gap-2"><input type="radio" name="period_type" value="{{ $val }}" {{ old('period_type', $filters['period_type'] ?? 'harian') == $val ? 'checked' : '' }} class="text-blue-600"> {{ $label }}</label>
                            @endforeach
                        </div>
                        @error('period_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $filters['start_date'] ?? date('Y-m-01')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $filters['end_date'] ?? date('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 text-sm font-medium">Tampilkan</button>
                </div>
            </form>
        </div>

        @if(isset($laporan) && $laporan->count() > 0)
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-900">Hasil Laporan</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('laporan.export', 'pdf') }}?format=pdf" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200">PDF</a>
                        <a href="{{ route('laporan.export', 'excel') }}?format=excel" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200">Excel</a>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total</p><p class="text-2xl font-bold">{{ $totalAkta }}</p></div>
                    <div class="bg-green-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Selesai</p><p class="text-2xl font-bold">{{ $totalSelesai }}</p></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
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
                                    <td class="px-4 py-3 text-sm"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $item->status_workflow }}</span></td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->tanggal_dibuat->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(isset($data) && isset($summary))
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-900">Hasil Laporan</h3>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">PDF</span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">Excel</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total</p><p class="text-2xl font-bold">{{ $summary['total'] }}</p></div>
                    @if(isset($summary['selesai']))
                        <div class="bg-green-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Selesai</p><p class="text-2xl font-bold">{{ $summary['selesai'] }}</p></div>
                    @endif
                    @if(isset($summary['total_aktas']))
                        <div class="bg-gray-50 p-4 rounded-lg"><p class="text-sm text-gray-500">Total Akta</p><p class="text-2xl font-bold">{{ $summary['total_aktas'] }}</p></div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                @if(($filters['report_type'] ?? 'akta') === 'akta')
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Klien</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                @else
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($data as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    @if(($filters['report_type'] ?? 'akta') === 'akta')
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->jenis_template }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->klien->nama_lengkap ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm"><span class="px-2 py-1 text-xs rounded-full bg-gray-100">{{ $item->status_workflow }}</span></td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->tanggal_dibuat->format('d/m/Y') }}</td>
                                    @else
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_lengkap }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nik }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nomor_telepon }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layout>
