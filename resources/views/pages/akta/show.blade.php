<x-layout>
    <x-slot name="title">Detail Akta</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Akta AKT-{{ str_pad($akta->id_akta, 4, '0', STR_PAD_LEFT) }} - {{ $akta->jenis_template }}</h2>
                <p class="mt-1 text-sm text-gray-600">Klien: {{ $akta->klien->nama_lengkap ?? '-' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($akta->templateAkta)
                    <a href="{{ route('akta.download', $akta->id_akta) }}" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-700">
                        <span class="material-symbols-outlined mr-2 text-[18px]">download</span>
                        Download Akta
                    </a>
                @endif
                <a href="{{ route('akta.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">Data Template Akta</h3>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                            {{ match($akta->status_workflow) {
                                'Draft' => 'bg-gray-100 text-gray-800',
                                'Diverifikasi' => 'bg-yellow-100 text-yellow-800',
                                'Final' => 'bg-blue-100 text-blue-800',
                                'Selesai' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-800',
                            } }}">
                            {{ $akta->status_workflow }}
                        </span>
                    </div>
                    <div class="px-6 py-6">
                        @php
                            $contentFields = $resolvedContentFields;
                            $rawContent = $akta->konten_teks_utama;
                        @endphp

                        @if(count($contentFields) > 0)
                            <div class="overflow-hidden rounded-md border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Tag</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($contentFields as $tag => $value)
                                            <tr>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-800">{{ $tag }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $value !== '' ? $value : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="min-h-[180px] whitespace-pre-wrap rounded-md border border-gray-200 bg-gray-50 p-4 text-sm leading-6 text-gray-800">
                                {{ $rawContent ?: 'Belum ada konten' }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Lampiran</h3>
                    </div>
                    <div class="px-6 py-6">
                        @if($akta->lampiran->count() > 0)
                            <div class="space-y-3">
                                @foreach($akta->lampiran as $lamp)
                                    <div class="flex items-center gap-3 rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                                        @if($lamp->format_extension === 'pdf')
                                            <span class="material-symbols-outlined flex-shrink-0 text-[22px] text-red-600">picture_as_pdf</span>
                                        @else
                                            <span class="material-symbols-outlined flex-shrink-0 text-[22px] text-blue-600">image</span>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-medium text-gray-900">{{ $lamp->nama_file }}</p>
                                            <p class="text-xs text-gray-500">{{ strtoupper($lamp->format_extension) }} - {{ $lamp->ukuran_berkas }} MB</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center">
                                <span class="material-symbols-outlined text-[36px] text-gray-400">folder_open</span>
                                <p class="mt-2 text-sm text-gray-500">Tidak ada lampiran</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Info</h3>
                    </div>
                    <div class="px-6 py-6 text-sm space-y-2">
                        <p><span class="text-gray-500">No. Resmi:</span> {{ $akta->repertorium->nomor_akta_resmi ?? 'Belum terbit' }}</p>
                        <p><span class="text-gray-500">Template:</span> {{ $akta->templateAkta->title ?? 'Belum dipilih' }}</p>
                        <p><span class="text-gray-500">Path Template:</span> {{ $akta->templateAkta->file_path ?? '-' }}</p>
                        <p><span class="text-gray-500">Dibuat:</span> {{ $akta->tanggal_dibuat->format('d/m/Y H:i') }}</p>
                        <p><span class="text-gray-500">Update:</span> {{ $akta->last_updated->format('d/m/Y H:i') }}</p>
                        <p><span class="text-gray-500">Oleh:</span> {{ $akta->user->nama_lengkap }}</p>
                        <p><span class="text-gray-500">Lampiran:</span> {{ $akta->lampiran->count() }} berkas</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Workflow</h3>
                    </div>
                    <div class="px-6 py-6">
                        <div class="space-y-3">
                            @foreach(['Draft', 'Diverifikasi', 'Final', 'Selesai'] as $step)
                                @php
                                    $statuses = ['Draft', 'Diverifikasi', 'Final', 'Selesai'];
                                    $currentIdx = array_search($akta->status_workflow, $statuses);
                                    $stepIdx = array_search($step, $statuses);
                                    $isCompleted = $stepIdx < $currentIdx;
                                    $isActive = $step === $akta->status_workflow;
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="h-3 w-3 rounded-full {{ $isActive ? 'bg-blue-600' : ($isCompleted ? 'bg-green-600' : 'bg-gray-300') }}"></div>
                                    <span class="text-sm {{ $isActive ? 'font-semibold text-blue-600' : ($isCompleted ? 'text-green-600' : 'text-gray-500') }}">{{ $step }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">Aksi</h3>
                    </div>
                    <div class="px-6 py-6 space-y-3">
                        @if($akta->status_workflow !== 'Selesai')
                            <a href="{{ route('akta.edit', $akta->id_akta) }}" class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                                <span class="material-symbols-outlined mr-2 text-[18px]">edit</span>
                                Edit Akta
                            </a>
                        @endif

                        @if($akta->templateAkta)
                            <a href="{{ route('akta.download', $akta->id_akta) }}" class="inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                                <span class="material-symbols-outlined mr-2 text-[18px]">download</span>
                                Download Dokumen
                            </a>
                        @endif

                        @if($akta->status_workflow === 'Draft')
                            <form method="POST" action="{{ route('akta.submit-verification', $akta->id_akta) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Ajukan untuk verifikasi?')" class="inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                                    <span class="material-symbols-outlined mr-2 text-[18px]">send</span>
                                    Ajukan Verifikasi
                                </button>
                            </form>
                        @endif

                        @if($akta->status_workflow === 'Diverifikasi' && Auth::user()->role === 'Notaris')
                            <form method="POST" action="{{ route('akta.set-final', $akta->id_akta) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Tetapkan Final? Repertorium akan digenerate.')" class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                                    <span class="material-symbols-outlined mr-2 text-[18px]">verified</span>
                                    Set Final
                                </button>
                            </form>
                        @endif

                        @if($akta->status_workflow === 'Diverifikasi' && in_array(Auth::user()->role, ['AdminStaff', 'Notaris']))
                            <form method="POST" action="{{ route('akta.revert-draft', $akta->id_akta) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Kembalikan ke Draft?')" class="inline-flex w-full items-center justify-center rounded-md bg-gray-500 px-4 py-2 text-sm text-white hover:bg-gray-600">
                                    <span class="material-symbols-outlined mr-2 text-[18px]">undo</span>
                                    Kembalikan ke Draft
                                </button>
                            </form>
                        @endif

                        @if($akta->status_workflow === 'Final' && Auth::user()->role === 'Notaris')
                            <form method="POST" action="{{ route('akta.set-selesai', $akta->id_akta) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Set Selesai? Akta akan terkunci.')" class="inline-flex w-full items-center justify-center rounded-md bg-green-700 px-4 py-2 text-sm text-white hover:bg-green-800">
                                    <span class="material-symbols-outlined mr-2 text-[18px]">lock</span>
                                    Set Selesai
                                </button>
                            </form>
                        @endif

                        @if(!in_array($akta->status_workflow, ['Final', 'Selesai']))
                            <x-confirm-modal :action="route('akta.destroy', $akta->id_akta)" message="Yakin ingin menghapus akta ini?" class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                                <span class="material-symbols-outlined mr-2 text-[18px]">delete</span>
                                Hapus Akta
                            </x-confirm-modal>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
