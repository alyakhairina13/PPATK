<x-layout>
    <x-slot name="title">Konfigurasi</x-slot>

    <!-- Page Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4.5">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Konfigurasi Sistem</h2>
            <p class="text-xs text-text-muted mt-0.5">Atur format penomoran repertorium resmi dan data PPAT global untuk template akta.</p>
        </div>
    </div>

    <!-- Data Form Card Container -->
    <div class="card p-6 max-w-4xl">
        <form method="POST" action="{{ route('konfigurasi.update') }}" id="konfigurasiForm">
            @csrf
            @method('PUT')

            <!-- PPAT Settings Section -->
            <div class="mb-6 border-b border-black/5 pb-6">
                <div class="mb-4">
                    <h3 class="text-xs font-bold text-slate-800 tracking-tight">Konfigurasi PPAT Global</h3>
                    <p class="text-[10px] text-text-muted mt-0.5">Nilai field berprefix <code>dppat</code> (grup <b>Data PPAT</b>) pada template akta akan terisi otomatis dari sini.</p>
                    @if(!$canManagePpatConfiguration)
                        <p class="text-[10px] text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded w-max mt-2">Hanya Notaris Utama yang dapat mengubah konfigurasi ini.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach($ppatTagKeys as $tag)
                        <div>
                            <label for="ppat_{{ $tag }}" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                                {{ \App\Services\TemplateAktaService::labelForTag($tag) }}
                            </label>
                            <input
                                type="text"
                                name="ppat_values[{{ $tag }}]"
                                id="ppat_{{ $tag }}"
                                value="{{ old("ppat_values.$tag", $ppatConfiguration[$tag] ?? '') }}"
                                class="w-full text-xs px-2.5 py-1.5 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container font-semibold disabled:bg-slate-50 disabled:text-slate-400"
                                {{ $canManagePpatConfiguration ? '' : 'disabled' }}>
                            @error("ppat_values.$tag")
                                <p class="mt-1 text-[10px] text-red-500 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    @if(empty($ppatTagKeys))
                        <p class="text-xs text-slate-400 md:col-span-2 py-2 bg-slate-50 rounded border border-black/5 text-center font-medium">Belum ada field berprefix <code>dppat</code> terdeteksi pada template.</p>
                    @endif
                </div>
            </div>

            <!-- Pattern and Numbering Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4.5 mb-6">
                <!-- Pattern Input -->
                <div class="flex flex-col">
                    <label for="pattern" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Format Penomoran</label>
                    <input type="text" name="pattern" id="pattern" value="{{ old('pattern', $konfigurasi->pattern) }}" 
                        class="text-xs px-2.5 py-1.5 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container font-semibold font-mono mb-2"
                        oninput="updatePreview()">
                    
                    <div class="flex gap-1.5 flex-wrap">
                        <button type="button" onclick="insertVariable('{NOMOR}')" class="text-[10px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 font-semibold px-2 py-0.5 rounded transition-colors">
                            {NOMOR}
                        </button>
                        <button type="button" onclick="insertVariable('{TAHUN}')" class="text-[10px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 font-semibold px-2 py-0.5 rounded transition-colors">
                            {TAHUN}
                        </button>
                        <button type="button" onclick="insertVariable('{BULAN}')" class="text-[10px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 font-semibold px-2 py-0.5 rounded transition-colors">
                            {BULAN}
                        </button>
                        <button type="button" onclick="insertVariable('/')" class="text-[10px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 font-semibold px-2.5 py-0.5 rounded transition-colors">
                            /
                        </button>
                        <button type="button" onclick="insertVariable('-')" class="text-[10px] bg-slate-100 hover:bg-slate-200 border border-black/5 text-slate-700 font-semibold px-2.5 py-0.5 rounded transition-colors">
                            -
                        </button>
                    </div>
                    @error('pattern')
                        <p class="mt-1 text-[10px] text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reset and Starting Number -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Reset Penomoran</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center text-xs text-slate-700 font-semibold cursor-pointer">
                                <input type="radio" name="reset_period" value="tahunan" 
                                    {{ old('reset_period', $konfigurasi->reset_period) == 'tahunan' ? 'checked' : '' }}
                                    class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-black/5 mr-2">
                                Tahunan
                            </label>
                            <label class="flex items-center text-xs text-slate-700 font-semibold cursor-pointer">
                                <input type="radio" name="reset_period" value="bulanan" 
                                    {{ old('reset_period', $konfigurasi->reset_period) == 'bulanan' ? 'checked' : '' }}
                                    class="h-3.5 w-3.5 text-blue-600 focus:ring-blue-500 border-black/5 mr-2">
                                Bulanan
                            </label>
                        </div>
                        @error('reset_period')
                            <p class="mt-1 text-[10px] text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="starting_number" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Awal</label>
                        <input type="number" name="starting_number" id="starting_number" 
                            value="{{ old('starting_number', $konfigurasi->starting_number) }}" 
                            min="1"
                            class="w-24 text-xs px-2.5 py-1 border border-black/5 bg-white rounded focus:outline-none focus:ring-1 focus:ring-primary-container font-semibold"
                            oninput="updatePreview()">
                        @error('starting_number')
                            <p class="mt-1 text-[10px] text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview Card (Completely Slate-based, no purple) -->
            <div class="mb-6 p-3.5 bg-blue-50/60 border border-blue-100 rounded-lg">
                <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Preview Penomoran Otomatis</label>
                <div class="text-base font-extrabold text-blue-800 font-mono tracking-wide" id="preview">{{ $konfigurasi->generatePreview() }}</div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-2 justify-end">
                <a href="{{ route('repertorium.index') }}" class="text-xs bg-slate-100 hover:bg-slate-200 border border-black/5 px-3.5 py-1.5 rounded-md font-semibold text-slate-800 transition-colors">Batal</a>
                <button type="button" onclick="confirmSave()" class="text-xs bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-1.5 rounded-md font-semibold shadow-2xs transition-colors">Simpan Setelan</button>
            </div>
        </form>
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

        // Initialize preview on page load
        document.addEventListener("DOMContentLoaded", function() {
            updatePreview();
        });
    </script>
</x-layout>
