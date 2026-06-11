<x-layout>
    <x-slot name="title">Buat Akta Baru</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Buat Akta Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">Buat draft akta baru lalu lanjutkan pengisian konten.</p>
                </div>
                <a href="{{ route('akta.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('akta.store') }}" class="px-6 py-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="id_klien" class="mb-2 block text-sm font-medium text-gray-700">Klien <span class="text-red-500">*</span></label>
                        <select name="id_klien" id="id_klien" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_klien') border-error @enderror" required>
                        <option value="">Pilih Klien</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id_klien }}" {{ old('id_klien') == $klien->id_klien ? 'selected' : '' }}>
                                {{ $klien->nama_lengkap }} ({{ $klien->nik }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_klien')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jenis_template" class="mb-2 block text-sm font-medium text-gray-700">Jenis Template <span class="text-red-500">*</span></label>
                        <select name="jenis_template" id="jenis_template" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenis_template') border-error @enderror" required>
                        <option value="">Pilih Jenis</option>
                        @foreach($jenisAkta as $jenis)
                            <option value="{{ $jenis }}" {{ old('jenis_template') == $jenis ? 'selected' : '' }}>
                                {{ $jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_template')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="konten_teks_utama" class="mb-2 block text-sm font-medium text-gray-700">Konten Akta</label>
                        <textarea
                            name="konten_teks_utama"
                            id="konten_teks_utama"
                            rows="10"
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[220px] @error('konten_teks_utama') border-error @enderror"
                            placeholder="Tulis isi draft akta di sini...">{{ old('konten_teks_utama') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Anda bisa mengisi konten sekarang atau melanjutkannya nanti di halaman edit.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('akta.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-300 px-6 py-2 text-sm text-gray-700 hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                    Simpan Draft
                </button>
            </div>
        </form>
    </div>
</x-layout>
