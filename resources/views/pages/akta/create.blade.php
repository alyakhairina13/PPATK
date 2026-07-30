<x-layout>
    <x-slot name="title">Buat Akta Baru</x-slot>

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Buat Akta Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">Pilih templat akta, isi kolom berdasarkan data yang terdeteksi, lalu simpan sebagai draf.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('akta.templates.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                        <span class="material-symbols-outlined mr-2 text-[18px]">library_add</span>
                        Kelola Templat
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
                    Belum ada templat akta. Tambahkan templat terlebih dahulu agar draf akta dapat dibuat.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="id_klien" class="mb-2 block text-sm font-medium text-gray-700">Pihak 1 <span class="text-red-500">*</span></label>
                    <select name="id_klien" id="id_klien" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_klien') border-error @enderror" required>
                        <option value="">Pilih Klien (Pihak 1)</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id_klien }}" {{ old('id_klien') == $klien->id_klien ? 'selected' : '' }}>
                                {{ $klien->nama_lengkap }} ({{ $klien->nik }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Data Pihak 1 akan terisi otomatis pada kolom <code>dpihak1</code>.</p>
                    @error('id_klien')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="id_klien_pihak2" class="mb-2 block text-sm font-medium text-gray-700">Pihak 2</label>
                    <select name="id_klien_pihak2" id="id_klien_pihak2" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_klien_pihak2') border-error @enderror">
                        <option value="">Pilih Klien (Pihak 2) - Opsional</option>
                        @foreach($kliens as $klien)
                            <option value="{{ $klien->id_klien }}" {{ old('id_klien_pihak2') == $klien->id_klien ? 'selected' : '' }}>
                                {{ $klien->nama_lengkap }} ({{ $klien->nik }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Isi jika akta melibatkan dua pihak. Data akan terisi otomatis pada kolom <code>dpihak2</code>.</p>
                    @error('id_klien_pihak2')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="template_id" class="mb-2 block text-sm font-medium text-gray-700">Templat Akta <span class="text-red-500">*</span></label>
                    <select name="template_id" id="template_id" class="w-full appearance-none rounded-md border border-gray-300 bg-white px-4 py-2 pr-10 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('template_id') border-error @enderror" required {{ $templates->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Pilih Templat</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id_template_akta }}" {{ (string) old('template_id') === (string) $template->id_template_akta ? 'selected' : '' }}>
                                {{ $template->title }} ({{ count($template->tags ?? []) }} kolom)
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Kolom di bawah akan menyesuaikan secara otomatis dengan isi templat.</p>
                    @error('template_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50">
                        <div class="border-b border-gray-200 px-4 py-3">
                            <h3 class="text-sm font-semibold text-gray-800">Kolom Data</h3>
                            <p class="mt-1 text-xs text-gray-500">Kolom dikelompokkan otomatis berdasarkan prefix placeholder (mis. <code>dppat</code> → <b>Data PPAT</b>) dengan label alias masing-masing.</p>
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
                    Simpan Draf
                </button>
            </div>
        </form>
    </div>

    <script>
        const templateOptions = @json($templateOptions);
        const lockedTemplateValues = @json($lockedTemplateValues);
        const initialValues = @json($formValues);
        const groupLabels = @json($prefixGroupLabels);
        const tagLabels = @json($tagLabels);
        const klienData = @json($klienData);
        const klienFieldMap = @json($klienFieldMap);
        const templateSelect = document.getElementById('template_id');
        const pihak1Select = document.getElementById('id_klien');
        const pihak2Select = document.getElementById('id_klien_pihak2');
        const fieldsContainer = document.getElementById('template-fields-container');

        const PIHAK_PREFIXES = ['dpihak1', 'dpihak2'];
        const LOCKED_PREFIXES = ['dpihak1'];
        let formValues = { ...@json($formValues) };

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function detectPrefix(tag) {
            const value = String(tag);
            const pos = value.indexOf('_');

            if (pos === -1 || pos === 0) {
                return null;
            }

            const token = value.slice(0, pos);

            return token !== '' ? token : null;
        }

        function groupPrefixForTag(tag) {
            return detectPrefix(tag) || 'lainnya';
        }

        function titleCase(value) {
            return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
        }

        function fallbackPrefixLabel(prefix) {
            if (prefix === 'lainnya') {
                return 'Data Lainnya';
            }

            let body = prefix;

            if (body.length > 1 && body.charAt(0) === 'd') {
                body = body.slice(1);
            }

            const match = body.match(/^(.+?)(\d+)$/);

            if (match) {
                return titleCase(match[1]) + ' ' + match[2];
            }

            return 'Data ' + titleCase(body);
        }

        function groupLabelForPrefix(prefix) {
            return groupLabels[prefix] || fallbackPrefixLabel(prefix);
        }

        function camelToLabel(value) {
            let text = String(value).replace(/[^a-zA-Z0-9]+/g, ' ');
            text = text.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
            text = text.replace(/([A-Z])([A-Z][a-z])/g, '$1 $2');

            return text.trim().split(/\s+/).filter(Boolean)
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }

        function labelForTag(tag) {
            if (Object.prototype.hasOwnProperty.call(tagLabels, tag)) {
                return tagLabels[tag];
            }

            const prefix = groupPrefixForTag(tag);
            const remainder = prefix !== 'lainnya'
                ? tag.slice(prefix.length).replace(/^_+/, '')
                : tag;

            return camelToLabel(remainder) || camelToLabel(tag);
        }

        function klienFieldForTag(tag) {
            const prefix = groupPrefixForTag(tag);

            if (!PIHAK_PREFIXES.includes(prefix)) {
                return null;
            }

            const suffix = tag.slice(prefix.length).replace(/^_+/, '');

            return Object.prototype.hasOwnProperty.call(klienFieldMap, suffix) ? klienFieldMap[suffix] : null;
        }

        function klienValueForTag(tag) {
            const field = klienFieldForTag(tag);

            if (field === null) {
                return null;
            }

            const prefix = groupPrefixForTag(tag);
            const selectedId = prefix === 'dpihak1' ? pihak1Select.value : pihak2Select.value;
            const klien = selectedId ? (klienData[selectedId] || null) : null;

            return klien && Object.prototype.hasOwnProperty.call(klien, field) ? klien[field] : '';
        }

        function renderField(tag) {
            const isPpatLocked = Object.prototype.hasOwnProperty.call(lockedTemplateValues, tag);
            const prefix = groupPrefixForTag(tag);
            const klienField = klienFieldForTag(tag);
            const isKlienLocked = klienField !== null && LOCKED_PREFIXES.includes(prefix);
            const isLocked = isPpatLocked || isKlienLocked;
            let value;

            if (isPpatLocked) {
                value = lockedTemplateValues[tag];
            } else if (isKlienLocked) {
                value = klienValueForTag(tag);
            } else {
                value = formValues[tag] ?? '';
            }

            const label = labelForTag(tag);

            return `
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">${escapeHtml(label)}</label>
                    <input
                        type="text"
                        name="template_fields[${escapeHtml(tag)}]"
                        value="${escapeHtml(value)}"
                        placeholder="Isi ${escapeHtml(label.toLowerCase())}"
                        class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 ${isLocked ? 'bg-gray-100' : ''}"
                        ${isLocked ? 'readonly' : ''}
                    >
                </div>
            `;
        }

        function captureFieldValues() {
            const values = {};

            fieldsContainer.querySelectorAll('input[name^="template_fields["]').forEach((input) => {
                const match = input.name.match(/template_fields\[(.+)\]/);
                if (match) {
                    values[match[1]] = input.value;
                }
            });

            return values;
        }

        function fillPihak2Fields() {
            const selectedId = pihak2Select.value;
            const klien = selectedId ? (klienData[selectedId] || null) : null;

            fieldsContainer.querySelectorAll('input[name^="template_fields["]').forEach((input) => {
                const match = input.name.match(/template_fields\[(.+)\]/);
                if (!match) return;

                const tag = match[1];
                if (groupPrefixForTag(tag) !== 'dpihak2') return;

                const field = klienFieldForTag(tag);
                if (field === null) return;

                if (klien && Object.prototype.hasOwnProperty.call(klien, field)) {
                    input.value = klien[field];
                }
            });
        }

        function renderTemplateFields() {
            Object.assign(formValues, captureFieldValues());

            const template = templateOptions[templateSelect.value];

            if (!template) {
                fieldsContainer.innerHTML = `
                    <div class="md:col-span-2 rounded-md border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-sm text-gray-500">
                        Pilih templat akta untuk menampilkan kolom yang harus diisi.
                    </div>
                `;
                return;
            }

            if (!template.tags.length) {
                fieldsContainer.innerHTML = `
                    <div class="md:col-span-2 rounded-md border border-dashed border-amber-300 bg-white px-4 py-8 text-center text-sm text-amber-700">
                        Templat ini belum memiliki kolom yang dapat diisi.
                    </div>
                `;
                return;
            }

            const groups = {};
            const order = [];
            template.tags.forEach((tag) => {
                const prefix = groupPrefixForTag(tag);
                if (!groups[prefix]) {
                    groups[prefix] = [];
                    order.push(prefix);
                }
                groups[prefix].push(tag);
            });

            fieldsContainer.innerHTML = order.map((prefix) => {
                const isPpatAutofill = groups[prefix].some((tag) => Object.prototype.hasOwnProperty.call(lockedTemplateValues, tag));
                const isKlienLocked = groups[prefix].some((tag) => klienFieldForTag(tag) !== null && LOCKED_PREFIXES.includes(prefix));
                const isKlienPrefill = groups[prefix].some((tag) => klienFieldForTag(tag) !== null && !LOCKED_PREFIXES.includes(prefix));
                let badge = '';

                if (isPpatAutofill) {
                    badge = '<span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200">Diisi otomatis dari Konfigurasi PPAT</span>';
                } else if (isKlienLocked) {
                    const source = groupLabelForPrefix(prefix);
                    badge = `<span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">Diisi otomatis dari data ${escapeHtml(source)}</span>`;
                } else if (isKlienPrefill) {
                    const source = groupLabelForPrefix(prefix);
                    badge = `<span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">Terisi otomatis dari data ${escapeHtml(source)} (dapat diubah)</span>`;
                }

                return `
                    <div class="md:col-span-2">
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-semibold text-gray-800">${escapeHtml(groupLabelForPrefix(prefix))}</h4>
                                ${badge}
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                ${groups[prefix].map(renderField).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            fillPihak2Fields();
        }

        templateSelect?.addEventListener('change', renderTemplateFields);
        pihak1Select?.addEventListener('change', renderTemplateFields);
        pihak2Select?.addEventListener('change', fillPihak2Fields);
        renderTemplateFields();
    </script>
</x-layout>
