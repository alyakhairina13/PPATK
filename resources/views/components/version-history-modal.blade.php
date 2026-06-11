<div id="versionHistoryModal" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 sm:p-6">
    <div class="card flex max-h-[calc(100vh-2rem)] w-full max-w-xl flex-col overflow-hidden mx-4 sm:max-h-[calc(100vh-3rem)]">
        <div class="flex items-start justify-between gap-3 border-b border-border-hairline pb-3">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-primary">
                    <span class="material-symbols-outlined text-[22px]">history</span>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-on-surface">Riwayat Versi</h3>
                    <p class="mt-1 text-sm text-text-muted">Telusuri perubahan terbaru pada dokumen.</p>
                </div>
            </div>
            <button type="button" onclick="closeVersionHistoryModal()" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border-hairline bg-surface-container-lowest text-text-muted hover:text-on-surface">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto pt-4">
            @if(isset($versions) && $versions->count() > 0)
                <div class="space-y-2.5">
                    @foreach($versions as $version)
                        <div class="rounded-lg border border-border-hairline bg-surface-container-lowest p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <span class="inline-flex items-center rounded-full bg-primary px-2.5 py-0.5 text-xs font-medium text-white">
                                    Versi {{ $version->version_number }}
                                </span>
                                <span class="text-xs text-text-muted">{{ $version->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs font-medium text-text-muted">Diubah oleh:</span>
                                <span class="ml-1 inline-flex items-center rounded-full bg-surface-pearl px-2 py-0.5 text-xs font-medium text-on-surface">
                                    {{ $version->user->name ?? '-' }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="text-xs font-medium text-text-muted">Deskripsi Perubahan:</span>
                                <p class="mt-1 text-sm text-on-surface">{{ $version->perubahan_deskripsi ?? 'Tidak ada deskripsi' }}</p>
                            </div>
                            <button type="button" onclick="toggleVersionContent({{ $version->id }})" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                Lihat Konten
                            </button>
                            <div id="versionContent{{ $version->id }}" class="hidden mt-3 rounded-md border border-border-hairline bg-surface-pearl p-3">
                                <pre class="whitespace-pre-wrap break-words font-mono text-xs leading-6 text-on-surface">{{ $version->konten_snapshot }}</pre>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-surface-pearl text-text-muted">
                        <span class="material-symbols-outlined text-[24px]">history</span>
                    </div>
                    <p class="mt-2 text-sm text-text-muted">Tidak ada riwayat versi</p>
                </div>
            @endif
        </div>

        <div class="mt-4 flex flex-col-reverse gap-2 border-t border-border-hairline pt-3 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeVersionHistoryModal()" class="btn-secondary w-full sm:w-auto">Tutup</button>
        </div>
    </div>
</div>

<script>
function openVersionHistoryModal() {
    document.getElementById('versionHistoryModal').classList.remove('hidden');
}
function closeVersionHistoryModal() {
    document.getElementById('versionHistoryModal').classList.add('hidden');
}
function toggleVersionContent(id) {
    const el = document.getElementById('versionContent' + id);
    el.classList.toggle('hidden');
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeVersionHistoryModal();
});
</script>
