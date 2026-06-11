<x-layout>
    <x-slot name="title">Detail Klien</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Profil Klien: {{ $klien->nama_lengkap }}</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('klien.edit', $klien->id_klien) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 rounded-md font-semibold text-xs text-white uppercase hover:bg-yellow-700">Edit</a>
                    <a href="{{ route('klien.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Kembali</a>
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Klien</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-sm font-medium text-gray-500">Nama Lengkap</label><p class="mt-1 text-base text-gray-900">{{ $klien->nama_lengkap }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">NIK</label><p class="mt-1 text-base text-gray-900">{{ $klien->nik }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">Tempat, Tanggal Lahir</label><p class="mt-1 text-base text-gray-900">{{ $klien->tempat_tanggal_lahir }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">Jenis Kelamin</label><p class="mt-1 text-base text-gray-900">{{ $klien->jenis_kelamin }}</p></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-500">Alamat</label><p class="mt-1 text-base text-gray-900">{{ $klien->alamat }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">Nomor Telepon</label><p class="mt-1 text-base text-gray-900">{{ $klien->nomor_telepon }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">Pekerjaan</label><p class="mt-1 text-base text-gray-900">{{ $klien->pekerjaan }}</p></div>
                <div><label class="block text-sm font-medium text-gray-500">NPWP</label><p class="mt-1 text-base text-gray-900">{{ $klien->npwp ?? '-' }}</p></div>
            </div>
        </div>

        <div class="px-6 py-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Akta</h3>
            @if($klien->akta && $klien->akta->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($klien->akta as $index => $akta)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $akta->jenis_template }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ match($akta->status_workflow) {
                                                'Draft' => 'bg-gray-100 text-gray-800',
                                                'Diverifikasi' => 'bg-yellow-100 text-yellow-800',
                                                'Final' => 'bg-blue-100 text-blue-800',
                                                'Selesai' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            } }}">{{ $akta->status_workflow }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $akta->tanggal_dibuat->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('akta.show', $akta->id_akta) }}" class="text-blue-600 hover:text-blue-900">Buka</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Belum ada riwayat akta untuk klien ini.</p>
                </div>
            @endif
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <x-confirm-modal :action="route('klien.destroy', $klien->id_klien)" message="Yakin ingin menghapus klien {{ $klien->nama_lengkap }}? Semua data akta terkait juga akan terpengaruh.">
                <span class="inline-flex items-center px-4 py-2 bg-red-600 rounded-md font-semibold text-xs text-white uppercase hover:bg-red-700">Hapus Data Klien</span>
            </x-confirm-modal>
        </div>
    </div>
</x-layout>
