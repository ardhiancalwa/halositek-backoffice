{{--
    Payment Action Modal (Approve / Decline)

    Usage: @include('admin.components._modal-payment')

    Open via JS:
      openPaymentModal('approve') or openPaymentModal('decline')
--}}

<div
    id="paymentModal"
    class="fixed inset-0 z-50 hidden"
    data-modal="payment"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity" data-modal-backdrop></div>

    {{-- Dialog --}}
    <div class="flex h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5" data-modal-dialog>

            {{-- Icon / Illustration --}}
            <div class="flex flex-col items-center px-8 pt-8 pb-2" data-payment-hero>
                {{-- Approve icon (shown by default) --}}
                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full" data-payment-icon-wrapper style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);">
                    <svg class="h-10 w-10" data-payment-icon fill="none" stroke="#10B981" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h3 class="text-xl font-extrabold text-slate-900" data-payment-title>Approve Payment?</h3>
                <p class="mt-2 text-center text-sm leading-relaxed text-slate-500" data-payment-description>
                    You are about to approve the payment for this consultation. This action will notify the architect and release the funds.
                </p>
            </div>

            {{-- Detail card --}}
            <div class="mx-8 mt-4 rounded-xl border border-slate-100 bg-[#FDFBF7] p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img
                            src="https://ui-avatars.com/api/?name=Adrian+Valerius&background=ececec&color=333333&rounded=true&bold=true&size=40"
                            class="h-10 w-10 rounded-full border border-slate-200 object-cover"
                            alt=""
                            data-payment-avatar
                        >
                        <div>
                            <p class="text-sm font-bold text-slate-800" data-payment-requester>Adrian Valerius</p>
                            <p class="text-[11px] text-slate-400" data-payment-date>Oct 24, 2023</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Amount</p>
                        <p class="text-lg font-black text-slate-900" data-payment-amount>Rp 25.000</p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 px-8 py-6" data-payment-actions>
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition-all hover:bg-slate-50 active:scale-[0.98]"
                    data-modal-close
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl px-6 py-3 text-sm font-bold text-white transition-all active:scale-[0.98] shadow-lg"
                    data-payment-confirm
                    style="background-color: #10B981; box-shadow: 0 10px 25px -8px rgba(16,185,129,0.5);"
                >
                    Yes, Approve
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [data-modal="payment"]:not(.hidden) [data-modal-dialog] {
        animation: paymentModalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes paymentModalPop {
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
