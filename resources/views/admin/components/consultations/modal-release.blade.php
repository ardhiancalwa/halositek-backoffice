{{--
    Release Payment Modal

    Usage: @include('admin.components.consultations.modal-release')
    Open via JS: openReleaseModal()
--}}

<div
    id="releaseModal"
    class="fixed inset-0 z-50 hidden"
    data-modal="release"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity" data-modal-backdrop></div>

    {{-- Dialog --}}
    <div class="flex h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5" data-modal-dialog>

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 pt-6 pb-4">
                <h2 class="text-lg font-extrabold text-slate-900">Release</h2>
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

            {{-- Linked Consultations --}}
            <div class="px-6">
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">Linked Consultations</p>

                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="pb-2 pr-4">User</th>
                            <th class="pb-2 pr-4">Date</th>
                            <th class="pb-2 pr-4 text-right">Fee</th>
                            <th class="pb-2 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr>
                            <td class="py-2.5 pr-4">
                                <span class="text-sm font-bold text-slate-800">Elena Rossi</span>
                            </td>
                            <td class="py-2.5 pr-4">
                                <span class="text-sm text-slate-400">Aug 12, 2023</span>
                            </td>
                            <td class="py-2.5 pr-4 text-right">
                                <span class="text-sm font-bold text-slate-800">25.000</span>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="text-[9px] font-black uppercase tracking-wider text-[#10B981]">Verified</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 pr-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-800">Marcus Low</span>
                                </div>
                            </td>
                            <td class="py-2.5 pr-4">
                                <span class="text-sm text-slate-400">Aug 15, 2023</span>
                            </td>
                            <td class="py-2.5 pr-4 text-right">
                                <span class="text-sm font-bold text-slate-800">25.000</span>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="text-[9px] font-black uppercase tracking-wider text-[#10B981]">Verified</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Payment Summary --}}
            <div class="mx-6 mt-5 border-t border-slate-100 pt-4">
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-[#E8820C]">Payment Summary</p>

                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Consultation Fee (Rp)/ hours</span>
                        <span class="text-sm font-bold text-slate-800">25.000</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Total User Consultations</span>
                        <span class="text-sm font-bold text-slate-800">2</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-sm font-extrabold uppercase text-slate-900">Total Amount</span>
                    <span class="text-lg font-black text-[#10B981]">Rp. 50.000</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 px-6 py-5">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 active:scale-[0.98]"
                    data-modal-close
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl bg-[#E8820C] px-6 py-3 text-sm font-bold text-white shadow-[0_10px_25px_-8px_rgba(232,130,12,0.5)] transition-all hover:bg-[#d0740a] active:scale-[0.98]"
                    data-release-confirm
                >
                    Release
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [data-modal="release"]:not(.hidden) [data-modal-dialog] {
        animation: releaseModalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes releaseModalPop {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>
