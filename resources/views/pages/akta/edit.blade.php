<x-layout>
    <x-slot name="title">Edit Akta</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Editor Akta - AKT-{{ str_pad($akta->id_akta, 4, '0', STR_PAD_LEFT) }}</h2>
                <p class="mt-1 text-sm text-gray-600">Perbarui isi akta, lampiran, dan lanjutkan workflow dari sini.</p>
            </div>
            <a href="{{ route('akta.show', $akta->id_akta) }}" class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Isi Akta</h3>
                    </div>

                    <form method="POST" action="{{ route('akta.update', $akta->id_akta) }}" id="aktaForm" class="px-6 py-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Klien <span class="text-red-500">*</span></label>
                                <select name="id_klien" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_klien') border-error @enderror" required>
                                    @foreach($kliens as $klien)
                                        <option value="{{ $klien->id_klien }}" {{ old('id_klien', $akta->id_klien) == $klien->id_klien ? 'selected' : '' }}>
                                            {{ $klien->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Jenis Template <span class="text-red-500">*</span></label>
                                <select name="jenis_template" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('jenis_template') border-error @enderror" required>
                                    @foreach($jenisAkta as $jenis)
                                        <option value="{{ $jenis }}" {{ old('jenis_template', $akta->jenis_template) == $jenis ? 'selected' : '' }}>
                                            {{ $jenis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Konten Akta</label>
                                <textarea
                                    name="konten_teks_utama"
                                    rows="16"
                                    class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 font-mono text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[320px] @error('konten_teks_utama') border-error @enderror">{{ old('konten_teks_utama', $akta->konten_teks_utama) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700">
                                <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                                Simpan Draft
                            </button>
                            @if($akta->status_workflow === 'Draft')
                                <button type="button" onclick="submitVerification()" class="inline-flex items-center justify-center rounded-md bg-green-600 px-6 py-2 text-sm text-white hover:bg-green-700">
                                    <span class="material-symbols-outlined mr-2 text-[18px]">send</span>
                                    Ajukan Verifikasi
                                </button>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Lampiran</h3>
                            <button type="button" onclick="openUploadModal()" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700">
                                <span class="material-symbols-outlined mr-2 text-[18px]">upload</span>
                                Upload
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        @if($akta->lampiran->count() > 0)
                            <div class="space-y-3">
                                @foreach($akta->lampiran as $lamp)
                                    <div class="flex items-center justify-between gap-3 rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if($lamp->format_extension === 'pdf')
                                                <span class="material-symbols-outlined flex-shrink-0 text-[22px] text-red-600">picture_as_pdf</span>
                                            @else
                                                <span class="material-symbols-outlined flex-shrink-0 text-[22px] text-blue-600">image</span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900">{{ $lamp->nama_file }}</p>
                                                <p class="text-xs text-gray-500">{{ strtoupper($lamp->format_extension) }} - {{ $lamp->ukuran_berkas }} MB</p>
                                            </div>
                                        </div>
                                        <x-confirm-modal :action="route('akta.lampiran.destroy', [$akta->id_akta, $lamp->id_dokumen])" message="Yakin ingin menghapus lampiran ini?">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </x-confirm-modal>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center">
                                <span class="material-symbols-outlined text-[36px] text-gray-400">folder_open</span>
                                <p class="mt-2 text-sm text-gray-500">Belum ada lampiran</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Info Klien</h3>
                    </div>
                    <div class="px-6 py-6 text-sm space-y-2">
                        <p><span class="text-gray-500">Nama:</span> {{ $akta->klien->nama_lengkap ?? '-' }}</p>
                        <p><span class="text-gray-500">NIK:</span> {{ $akta->klien->nik ?? '-' }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Riwayat Versi</h3>
                    </div>
                    <div class="px-6 py-6">
                        @if($akta->versionHistory->count() > 0)
                            <div class="space-y-3">
                                @foreach($akta->versionHistory->take(5) as $ver)
                                    <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-gray-900">v{{ $ver->versi_ke }}</span>
                                            <span class="text-gray-500">{{ $ver->timestamp_perubahan->format('d/m H:i') }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">{{ $ver->diubah_oleh }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Belum ada riwayat</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitVerification() {
            if (confirm('Ajukan akta ini untuk verifikasi?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("akta.submit-verification", $akta->id_akta) }}';
                form.innerHTML = '@csrf <input type="hidden" name="_method" value="POST">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</x-layout>
