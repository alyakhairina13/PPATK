<x-layout>
    <x-slot name="title">Buat Akta Baru</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Buat Akta Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">Pilih template akta, isi field berdasarkan tag yang terdeteksi, lalu simpan sebagai draft.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('akta.templates.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                        <span class="material-symbols-outlined mr-2 text-[18px]">library_add</span>
                        Kelola Template
                    </a>
                    <a href="{{ route('akta.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                        <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('akta.store') }}" class="px-6 py-6">
            @csrf

            @if($templates->isEmpty())
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                    Belum ada template akta. Tambahkan template dulu agar draft akta bisa dibuat.
                </div>
            @endif

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
                    <label for="template_id" class="mb-2 block text-sm font-medium text-gray-700">Template Akta <span class="text-red-500">*</span></label>
                    <select name="template_id" id="template_id" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('template_id') border-error @enderror" required {{ $templates->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Pilih Template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id_template_akta }}" {{ (string) old('template_id') === (string) $template->id_template_akta ? 'selected' : '' }}>
                                {{ $template->title }} ({{ count($template->tags ?? []) }} tag)
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Field di bawah akan menyesuaikan otomatis dengan tag yang ada di template.</p>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50">
                        <div class="border-b border-gray-200 px-4 py-3">
                            <h3 class="text-sm font-semibold text-gray-800">Field Template</h3>
                            <p class="mt-1 text-xs text-gray-500">Placeholder seperti <code>&#123;&#123;$nama_pihak&#125;&#125;</code> akan menjadi input otomatis di sini.</p>
                        </div>
                        <div id="template-fields-container" class="grid grid-cols-1 gap-4 px-4 py-4 md:grid-cols-2"></div>
                    </div>
                    @error('template_fields')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('akta.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-300 px-6 py-2 text-sm text-gray-700 hover:bg-gray-400">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-6 py-2 text-sm text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400" {{ $templates->isEmpty() ? 'disabled' : '' }}>
                    <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                    Simpan Draft
                </button>
            </div>
        </form>
    </div>

    <script>
        const templateOptions = @json($templateOptions);
        const lockedTemplateValues = @json($lockedTemplateValues);
        const initialValues = @json($formValues);
        const templateSelect = document.getElementById('template_id');
        const fieldsContainer = document.getElementById('template-fields-container');

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatLabel(tag) {
            return tag
                .replace(/[_-]+/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());
        }

        function renderTemplateFields() {
            const template = templateOptions[templateSelect.value];

            if (!template) {
                fieldsContainer.innerHTML = `
                    <div class="md:col-span-2 rounded-md border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-sm text-gray-500">
                        Pilih template akta untuk menampilkan field yang harus diisi.
                    </div>
                `;
                return;
            }

            if (!template.tags.length) {
                fieldsContainer.innerHTML = `
                    <div class="md:col-span-2 rounded-md border border-dashed border-amber-300 bg-white px-4 py-8 text-center text-sm text-amber-700">
                        Template ini belum memiliki tag yang bisa diisi.
                    </div>
                `;
                return;
            }

            fieldsContainer.innerHTML = template.tags.map((tag) => {
                const isLocked = Object.prototype.hasOwnProperty.call(lockedTemplateValues, tag);
                const value = isLocked ? lockedTemplateValues[tag] : (initialValues[tag] ?? '');

                return `
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">${escapeHtml(formatLabel(tag))}</label>
                        <input
                            type="text"
                            name="template_fields[${escapeHtml(tag)}]"
                            value="${escapeHtml(value)}"
                            placeholder="Isi ${escapeHtml(formatLabel(tag).toLowerCase())}"
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 ${isLocked ? 'bg-gray-100' : ''}"
                            ${isLocked ? 'readonly' : ''}
                        >
                        <p class="mt-1 text-xs text-gray-500">Tag: &#123;&#123;$${escapeHtml(tag)}&#125;&#125;${isLocked ? ' - otomatis dari konfigurasi PPAT' : ''}</p>
                    </div>
                `;
            }).join('');
        }

        templateSelect?.addEventListener('change', renderTemplateFields);
        renderTemplateFields();
    </script>
</x-layout>
