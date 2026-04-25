@php
    $title = $title ?? 'Modal';
    $widthClass = $widthClass ?? 'max-w-lg';
    $panelClass = $panelClass ?? '';
    $closeLabel = $closeLabel ?? 'Close modal';
@endphp

<div
    id="{{ $id }}"
    data-modal-root
    class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <div data-modal-overlay class="absolute inset-0 bg-slate-950/35 backdrop-blur-[2px]"></div>

    <div class="relative z-10 w-full {{ $widthClass }}">
        <div class="overflow-hidden rounded-3xl bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] {{ $panelClass }}">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="{{ $id }}-title" class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $title }}
                </h2>

                <button
                    type="button"
                    data-modal-close
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="{{ $closeLabel }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="px-5 py-5 sm:px-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
