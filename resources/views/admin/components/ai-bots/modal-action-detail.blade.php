<div
    id="aiBotActionModal"
    class="fixed inset-0 z-50 hidden"
    data-modal="ai-bot-action"
>
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity" data-modal-backdrop></div>

    <div class="flex h-full items-center justify-center p-4">
        <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5" data-modal-dialog>
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" data-ai-modal-status-icon>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                    <h3 class="text-xl font-black tracking-tight text-slate-900" data-ai-modal-title>Generation Success Actions</h3>
                </div>

                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    data-modal-close
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="max-h-[78vh] space-y-6 overflow-y-auto p-6">
                <div>
                    <p class="mb-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Original Request</p>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm leading-relaxed text-slate-600">
                        <p data-ai-modal-request>"Prompt details..."</p>
                    </div>
                </div>

                <div data-ai-modal-failure class="hidden">
                    <p class="mb-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">System Error Log</p>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                        <p class="mb-2 font-mono text-[11px] font-bold text-rose-600" data-ai-modal-error-title>[ERROR] System issue</p>
                        <p class="font-mono text-[11px] leading-relaxed text-rose-500" data-ai-modal-error-detail>No diagnostic details.</p>
                    </div>
                </div>

                <div data-ai-modal-success>
                    <p class="mb-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Generated Output</p>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <div class="overflow-hidden rounded-lg border border-emerald-100 bg-white">
                            <img
                                src=""
                                alt="Generated output"
                                class="h-72 w-full object-cover"
                                data-ai-modal-output-image
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [data-modal="ai-bot-action"]:not(.hidden) [data-modal-dialog] {
        animation: aiBotActionModalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes aiBotActionModalPop {
        from {
            opacity: 0;
            transform: scale(0.96) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>
