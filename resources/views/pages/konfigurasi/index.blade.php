<x-layout>
    <x-slot name="title">Konfigurasi</x-slot>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">Konfigurasi Sistem</h1>
                <p class="mt-2 text-sm text-gray-700">Atur format penomoran repertorium dan data PPAT global untuk template akta.</p>
            </div>
        </div>

        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <form method="POST" action="{{ route('konfigurasi.update') }}" id="konfigurasiForm">
                @csrf
                @method('PUT')

                <div class="mb-8 border-b border-gray-200 pb-8">
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">Konfigurasi PPAT</h2>
                        <p class="mt-2 text-sm text-gray-600">Nilai field berprefix <code>dppat</code> (grup <b>Data PPAT</b>) pada template akta akan terisi otomatis dari sini dan dikunci di form akta.</p>
                        @if(!$canManagePpatConfiguration)
                            <p class="mt-2 text-sm text-amber-700">Hanya notaris yang dapat mengubah konfigurasi PPAT. Anda masih bisa melihat nilainya di sini.</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        @foreach($ppatTagKeys as $tag)
                            <div>
                                <label for="ppat_{{ $tag }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ \App\Services\TemplateAktaService::labelForTag($tag) }}
                                </label>
                                <input
                                    type="text"
                                    name="ppat_values[{{ $tag }}]"
                                    id="ppat_{{ $tag }}"
                                    value="{{ old("ppat_values.$tag", $ppatConfiguration[$tag] ?? '') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border disabled:bg-gray-50 disabled:text-gray-500"
                                    {{ $canManagePpatConfiguration ? '' : 'disabled' }}>
                                @error("ppat_values.$tag")
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        @if(empty($ppatTagKeys))
                            <p class="text-sm text-gray-500 md:col-span-2">Belum ada field berprefix <code>dppat</code> terdeteksi pada template. Unggah template dengan placeholder seperti <code>&#123;&#123;$dppat_name&#125;&#125;</code> agar muncul di sini.</p>
                        @endif
                    </div>
                </div>

                <!-- Pattern Input -->
                <div class="mb-6">
                    <label for="pattern" class="block text-sm font-medium text-gray-700 mb-2">Format Penomoran</label>
                    <div class="flex gap-2 mb-2">
                        <input type="text" name="pattern" id="pattern" value="{{ old('pattern', $konfigurasi->pattern) }}" 
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border"
                            oninput="updatePreview()">
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button type="button" onclick="insertVariable('{NOMOR}')" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            {NOMOR}
                        </button>
                        <button type="button" onclick="insertVariable('{TAHUN}')" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            {TAHUN}
                        </button>
                        <button type="button" onclick="insertVariable('{BULAN}')" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            {BULAN}
                        </button>
                        <button type="button" onclick="insertVariable('/')" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            /
                        </button>
                        <button type="button" onclick="insertVariable('-')" class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded hover:bg-gray-200">
                            -
                        </button>
                    </div>
                    @error('pattern')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reset Period -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reset Penomoran</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" id="tahunan" name="reset_period" value="tahunan" 
                                {{ old('reset_period', $konfigurasi->reset_period) == 'tahunan' ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="tahunan" class="ml-3 block text-sm text-gray-700">
                                Tahunan (reset setiap tahun)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="bulanan" name="reset_period" value="bulanan" 
                                {{ old('reset_period', $konfigurasi->reset_period) == 'bulanan' ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="bulanan" class="ml-3 block text-sm text-gray-700">
                                Bulanan (reset setiap bulan)
                            </label>
                        </div>
                    </div>
                    @error('reset_period')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Starting Number -->
                <div class="mb-6">
                    <label for="starting_number" class="block text-sm font-medium text-gray-700 mb-2">Nomor Awal</label>
                    <input type="number" name="starting_number" id="starting_number" 
                        value="{{ old('starting_number', $konfigurasi->starting_number) }}" 
                        min="1"
                        class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 border"
                        oninput="updatePreview()">
                    @error('starting_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
                    <div class="text-lg font-mono text-blue-900" id="preview">{{ $konfigurasi->generatePreview() }}</div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <button type="button" onclick="confirmSave()"
                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan
                    </button>
                    <a href="{{ route('repertorium.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmSave() {
            if (confirm('Apakah Anda yakin ingin menyimpan perubahan konfigurasi?')) {
                document.getElementById('konfigurasiForm').submit();
            }
        }

        function insertVariable(variable) {
            const input = document.getElementById('pattern');
            const start = input.selectionStart;
            const end = input.selectionEnd;
            const text = input.value;
            input.value = text.substring(0, start) + variable + text.substring(end);
            input.focus();
            input.setSelectionRange(start + variable.length, start + variable.length);
            updatePreview();
        }

        function updatePreview() {
            const pattern = document.getElementById('pattern').value;
            const startingNumber = parseInt(document.getElementById('starting_number').value) || 1;
            
            const now = new Date();
            const nomor = String(startingNumber).padStart(3, '0');
            const tahun = now.getFullYear();
            const bulan = String(now.getMonth() + 1).padStart(2, '0');
            
            let preview = pattern
                .replace(/{NOMOR}/g, nomor)
                .replace(/{TAHUN}/g, tahun)
                .replace(/{BULAN}/g, bulan);
            
            document.getElementById('preview').textContent = preview;
        }

        // Initial preview update
        updatePreview();
    </script>
</x-layout>
