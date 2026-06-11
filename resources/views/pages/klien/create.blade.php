<x-layout>
    <x-slot name="title">Tambah Klien</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Tambah Data Klien</h2>
                <a href="{{ route('klien.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('klien.store') }}" class="px-6 py-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama_lengkap') border-red-500 @enderror" required>
                    @error('nama_lengkap') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">NIK (16 digit) <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" pattern="[0-9]{16}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nik') border-red-500 @enderror" required>
                    @error('nik') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="tempat_tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">Tempat, Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="text" name="tempat_tanggal_lahir" id="tempat_tanggal_lahir" value="{{ old('tempat_tanggal_lahir') }}" placeholder="Contoh: Jakarta, 01 Januari 1990" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tempat_tanggal_lahir') border-red-500 @enderror" required>
                    @error('tempat_tanggal_lahir') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="jenis_kelamin" id="jenis_kelamin" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenis_kelamin') border-red-500 @enderror" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                    <textarea name="alamat" id="alamat" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('alamat') border-red-500 @enderror" required>{{ old('alamat') }}</textarea>
                    @error('alamat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nomor_telepon" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon <span class="text-red-500">*</span></label>
                    <input type="text" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon') }}" placeholder="081234567890" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nomor_telepon') border-red-500 @enderror" required>
                    @error('nomor_telepon') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan <span class="text-red-500">*</span></label>
                    <input type="text" name="pekerjaan" id="pekerjaan" value="{{ old('pekerjaan') }}" placeholder="Contoh: Wiraswasta" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pekerjaan') border-red-500 @enderror" required>
                    @error('pekerjaan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="npwp" class="block text-sm font-medium text-gray-700 mb-2">NPWP (Opsional)</label>
                    <input type="text" name="npwp" id="npwp" value="{{ old('npwp') }}" placeholder="12.345.678.9-012.345" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('npwp') border-red-500 @enderror">
                    @error('npwp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('klien.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Batal</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('nik').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9]/g, ''); });
        document.getElementById('nomor_telepon').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9+]/g, ''); });
    </script>
</x-layout>
