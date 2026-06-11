<div id="uploadModal" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 sm:p-6">
    <div class="card w-full max-w-md mx-4">
        <form method="POST" action="{{ $action ?? '' }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-primary">
                        <span class="material-symbols-outlined text-[22px]">upload_file</span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-on-surface">Upload Lampiran</h3>
                        <p class="mt-1 text-sm text-text-muted">Tambahkan dokumen pendukung untuk data akta ini.</p>
                    </div>
                </div>
                <button type="button" onclick="closeUploadModal()" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-border-hairline bg-surface-container-lowest text-text-muted hover:text-on-surface">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>

            <div class="space-y-3">
                <div>
                    <label for="nama_dokumen" class="mb-1 block text-sm font-medium text-on-surface">
                        Nama Dokumen <span class="text-error">*</span>
                    </label>
                    <input type="text"
                           name="nama_dokumen"
                           id="nama_dokumen"
                           class="input-field @error('nama_dokumen') border-error @enderror"
                           placeholder="Contoh: KTP Klien, Sertifikat Tanah"
                           required>
                    @error('nama_dokumen')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="file" class="mb-1 block text-sm font-medium text-on-surface">
                        File <span class="text-error">*</span>
                    </label>
                    <input type="file"
                           name="file"
                           id="file"
                           class="input-field @error('file') border-error @enderror"
                           accept=".jpg,.jpeg,.png,.pdf"
                           required
                           onchange="previewFile(this)">
                    @error('file')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 flex items-center gap-1 text-xs text-text-muted">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        Format yang didukung: JPG, PNG, PDF | Maksimal: 10MB
                    </p>
                </div>

                <div id="filePreview" class="hidden">
                    <div class="flex items-center gap-3 rounded-lg border border-border-hairline bg-surface-container-lowest p-3 text-on-surface">
                        <span class="material-symbols-outlined text-[28px] text-primary">description</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium" id="fileName"></p>
                            <p class="text-xs text-text-muted" id="fileSize"></p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="mb-1 block text-sm font-medium text-on-surface">Keterangan</label>
                    <textarea name="keterangan"
                              id="keterangan"
                              class="input-field @error('keterangan') border-error @enderror"
                              rows="3"
                              placeholder="Keterangan tambahan (opsional)"></textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-border-hairline pt-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeUploadModal()" class="btn-secondary w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                    Batal
                </button>
                <button type="submit" class="btn-primary w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[18px]">upload</span>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}
function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}
function previewFile(input) {
    const preview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSizeKB = (file.size / 1024).toFixed(2);
        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);

        fileName.textContent = file.name;
        fileSize.textContent = fileSizeMB > 1
            ? fileSizeMB + ' MB'
            : fileSizeKB + ' KB';

        preview.classList.remove('hidden');

        if (file.size > 10 * 1024 * 1024) {
            alert('Ukuran file melebihi 10MB. Silakan pilih file yang lebih kecil.');
            input.value = '';
            preview.classList.add('hidden');
        }
    } else {
        preview.classList.add('hidden');
    }
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeUploadModal();
});
</script>
