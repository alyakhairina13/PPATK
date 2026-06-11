<x-layout>
    <x-slot name="title">Detail Repertorium</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Detail Repertorium</h2>
        <a href="{{ route('repertorium.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">Kembali</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Repertorium</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Nomor Resmi</dt><dd class="font-medium">{{ $repertorium->nomor_akta_resmi }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Indeks</dt><dd class="font-medium">{{ str_pad($repertorium->indeks_urutan, 3, '0', STR_PAD_LEFT) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tahun</dt><dd class="font-medium">{{ $repertorium->tahun_buku }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Bulan</dt><dd class="font-medium">{{ $repertorium->bulan_buku }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tgl Generasi</dt><dd class="font-medium">{{ $repertorium->timestamp_generasi->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Akta</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Jenis</dt><dd class="font-medium">{{ $repertorium->akta->jenis_template ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ $repertorium->akta->status_workflow ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Dibuat</dt><dd class="font-medium">{{ $repertorium->akta->tanggal_dibuat?->format('d/m/Y') ?? '-' }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Klien</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Nama</dt><dd class="font-medium">{{ $repertorium->akta->klien->nama_lengkap ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">NIK</dt><dd class="font-medium">{{ $repertorium->akta->klien->nik ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Telepon</dt><dd class="font-medium">{{ $repertorium->akta->klien->nomor_telepon ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Alamat</dt><dd class="font-medium">{{ $repertorium->akta->klien->alamat ?? '-' }}</dd></div>
            </dl>
        </div>
    </div>
</x-layout>
