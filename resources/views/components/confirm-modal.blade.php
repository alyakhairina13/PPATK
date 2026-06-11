@props(['action', 'message' => 'Apakah Anda yakin?', 'title' => 'Konfirmasi', 'confirmText' => 'Ya, Lanjutkan', 'cancelText' => 'Batal', 'method' => 'DELETE'])

@php
$modalId = 'confirm-' . md5($action . $message);
@endphp

<button type="button" onclick="openConfirmModal('{{ $modalId }}')" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-1.5 text-error transition-colors hover:text-red-800']) }}>
    {{ $slot }}
</button>

<div id="{{ $modalId }}" class="hidden fixed inset-0 modal-overlay z-50 flex items-center justify-center p-4 sm:p-6">
    <div class="card w-full max-w-sm mx-4">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-error">
                <span class="material-symbols-outlined text-[22px]">warning</span>
            </div>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-on-surface">{{ $title }}</h3>
                <p class="mt-1 text-sm leading-6 text-text-muted">{{ $message }}</p>
            </div>
        </div>
        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" onclick="closeConfirmModal('{{ $modalId }}')" class="btn-secondary w-full sm:w-auto">{{ $cancelText }}</button>
            <form method="POST" action="{{ $action }}" class="w-full sm:w-auto">
                @csrf
                @method($method)
                <button type="submit" class="btn-danger w-full sm:w-auto">{{ $confirmText }}</button>
            </form>
        </div>
    </div>
</div>

<script>
function openConfirmModal(id) {
    document.getElementById(id).classList.remove('hidden');
}
function closeConfirmModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
