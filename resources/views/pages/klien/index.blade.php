<x-layout>
    <x-slot name="title">Data Klien</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Data Klien</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('klien.import') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <span class="material-symbols-outlined text-[18px] mr-2">upload_file</span>
                        Import Excel
                    </a>
                    <a href="{{ route('klien.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <span class="material-symbols-outlined text-[18px] mr-2">person_add</span>
                        Tambah Klien
                    </a>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="{{ route('klien.index') }}" id="searchForm">
                <div class="flex items-center space-x-4">
                    <div class="flex-1">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">search</span>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Cari berdasarkan Nama atau NIK..." class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('klien.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Kelamin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pekerjaan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($kliens as $index => $klien)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $kliens->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $klien->nik }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $klien->nama_lengkap }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $klien->jenis_kelamin }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $klien->nomor_telepon }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $klien->pekerjaan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('klien.show', $klien->id_klien) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <a href="{{ route('klien.edit', $klien->id_klien) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <x-confirm-modal :action="route('klien.destroy', $klien->id_klien)" message="Yakin ingin menghapus klien {{ $klien->nama_lengkap }}?" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                {{ request('search') ? 'Tidak ada data yang sesuai pencarian' : 'Belum ada data klien' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kliens->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">{{ $kliens->links() }}</div>
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
