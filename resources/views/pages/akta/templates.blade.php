<x-layout>
    <x-slot name="title">Template Akta</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Template Akta</h2>
                <p class="mt-1 text-sm text-gray-600">Unggah file <code>.doc</code> atau <code>.docx</code>. Placeholder dengan prefix (mis. <code>&#123;&#123;$dppat_name&#125;&#125;</code>) akan otomatis dikelompokkan dan diberi alias label.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('akta.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Kembali ke Akta
                </a>
                <a href="{{ route('akta.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">note_add</span>
                    Buat Akta
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="xl:col-span-1">
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">Tambah Template</h3>
                    </div>

                    <form method="POST" action="{{ route('akta.templates.store') }}" enctype="multipart/form-data" class="space-y-5 px-6 py-6">
                        @csrf

                        <div>
                            <label for="title" class="mb-2 block text-sm font-medium text-gray-700">Nama Template <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Contoh: Akta AJB Rumah"
                                class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                                required
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="template_file" class="mb-2 block text-sm font-medium text-gray-700">File Template <span class="text-red-500">*</span></label>
                            <input
                                type="file"
                                id="template_file"
                                name="template_file"
                                accept=".doc,.docx"
                                class="block w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 @error('template_file') border-red-500 @enderror"
                                required
                            >
                            <p class="mt-1 text-xs text-gray-500">Gunakan file <code>.doc</code> atau <code>.docx</code>. File <code>.docs</code> tidak didukung. Beri awalan prefix pada placeholder untuk pengelompokan otomatis, mis. <code>&#123;&#123;$dppat_name&#125;&#125;</code> (grup <b>Data PPAT</b>), <code>&#123;&#123;$dseller_name&#125;&#125;</code> (grup <b>Data Penjual</b>), <code>&#123;&#123;$dbuyer_name&#125;&#125;</code> (grup <b>Data Pembeli</b>), <code>&#123;&#123;$dwitness1_name&#125;&#125;</code> (grup <b>Data Saksi</b>), <code>&#123;&#123;$dland_nib&#125;&#125;</code> (grup <b>Data Objek Tanah</b>), <code>&#123;&#123;$dlocation_province&#125;&#125;</code> (grup <b>Data Lokasi</b>), atau <code>&#123;&#123;$dtrx_price_number&#125;&#125;</code> (grup <b>Data Transaksi</b>).</p>
                            @error('template_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <span class="material-symbols-outlined mr-2 text-[18px]">upload_file</span>
                            Simpan Template
                        </button>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Template</h3>
                    </div>

                    <div class="px-6 py-6">
                        @forelse($templates as $template)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-5 py-4 {{ $loop->last ? '' : 'mb-4' }}">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900">{{ $template->title }}</h4>
                                        <p class="mt-1 text-sm text-gray-600">File: {{ $template->original_filename }} -> {{ $template->file_path }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-wide text-gray-500">{{ strtoupper($template->file_extension) }} - {{ count($template->tags ?? []) }} tag terdeteksi - {{ $template->akta_count }} akta</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                            {{ $template->slug }}
                                        </span>

                                        @if($template->akta_count === 0)
                                            <x-confirm-modal
                                                :action="route('akta.templates.destroy', $template->id_template_akta)"
                                                :message="'Yakin ingin menghapus template '.$template->title.'?'"
                                                confirmText="Hapus Template"
                                            >
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </x-confirm-modal>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                                Sedang dipakai
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    @php
                                        $groupedTags = $template->grouped_tags;
                                    @endphp
                                    @foreach($groupedTags as $prefix => $tags)
                                        <div>
                                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                {{ \App\Services\TemplateAktaService::groupLabelForPrefix($prefix) }}
                                                <span class="ml-1 font-normal normal-case text-gray-400">({{ count($tags) }})</span>
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($tags as $tag)
                                                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200" title="${{ $tag }}">
                                                        {{ \App\Services\TemplateAktaService::labelForTag($tag) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-4 py-10 text-center">
                                <span class="material-symbols-outlined text-[36px] text-gray-400">description</span>
                                <p class="mt-2 text-sm text-gray-500">Belum ada template akta yang tersimpan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800">Kelola Alias Label</h3>
                <p class="mt-1 text-sm text-gray-600">Atur label grup (prefix) dan label tiap field (tag). Prefix dideteksi otomatis dari bagian nama sebelum underscore pertama (angka di akhir diabaikan, mis. <code>dwitness1_name</code> → <code>dwitness</code>).</p>
            </div>

            <form method="POST" action="{{ route('akta.templates.aliases.update') }}" class="space-y-6 px-6 py-6">
                @csrf
                @method('PUT')

                <div>
                    <h4 class="text-sm font-semibold text-gray-800">Alias Grup (Prefix)</h4>
                    <p class="mt-1 text-xs text-gray-500">Kosongkan untuk memakai label otomatis (terlihat sebagai placeholder).</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($prefixKeys as $prefix)
                            <div class="flex items-center gap-3">
                                <span class="inline-flex w-32 shrink-0 items-center rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-600" title="${{ $prefix }}_">${{ $prefix }}_</span>
                                <input
                                    type="text"
                                    name="prefix_aliases[{{ $prefix }}]"
                                    value="{{ $prefixAliases[$prefix] ?? '' }}"
                                    placeholder="{{ \App\Services\TemplateAktaService::groupLabelForPrefix($prefix) }}"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        @endforeach
                        @if(empty($prefixKeys))
                            <p class="text-sm text-gray-500">Belum ada prefix terdeteksi. Unggah template dengan placeholder berprefix terlebih dahulu.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-800">Alias Tag (Field)</h4>
                    <p class="mt-1 text-xs text-gray-500">Label ini dipakai pada form pembuatan/pengubahan akta dan halaman detail.</p>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($tagKeys as $tag)
                            <div class="flex items-center gap-3">
                                <span class="inline-flex w-48 shrink-0 truncate rounded bg-gray-100 px-2 py-1 font-mono text-xs text-gray-600" title="${{ $tag }}">${{ $tag }}</span>
                                <input
                                    type="text"
                                    name="tag_aliases[{{ $tag }}]"
                                    value="{{ $tagAliases[$tag] ?? '' }}"
                                    placeholder="{{ \App\Services\TemplateAktaService::labelForTag($tag) }}"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                        @endforeach
                        @if(empty($tagKeys))
                            <p class="text-sm text-gray-500">Belum ada tag terdeteksi.</p>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                        Simpan Alias
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
