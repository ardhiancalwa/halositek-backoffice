{{--
    Consultation Transcript Modal
    
    Usage: @include('admin.components._modal-transcript')
    
    Open via JS: document.getElementById('transcriptModal').classList.remove('hidden')
    Close via JS: document.getElementById('transcriptModal').classList.add('hidden')
--}}

<div
    id="transcriptModal"
    class="fixed inset-0 z-50 hidden"
    data-modal="transcript"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] transition-opacity" data-modal-backdrop></div>

    {{-- Dialog --}}
    <div class="flex h-full items-center justify-center p-4">
        <div class="relative flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5" data-modal-dialog>
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#FFF5EA]">
                        <svg class="h-4.5 w-4.5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Consultation Transcript</h2>
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

            {{-- Participants bar --}}
            <div class="border-b border-slate-100 px-6 py-3">
                <p class="mb-2 text-[9px] font-black uppercase tracking-widest text-[#E8820C]">Requester</p>
                <div class="flex items-center gap-4">
                    {{-- Requester --}}
                    <div class="flex items-center gap-2">
                        <img
                            src="https://ui-avatars.com/api/?name=Adrian+Valerius&background=ececec&color=333333&rounded=true&bold=true&size=32"
                            class="h-7 w-7 rounded-full border border-slate-200 object-cover"
                            alt="Adrian Valerius"
                            data-transcript-requester-avatar
                        >
                        <span class="text-xs font-bold text-slate-700" data-transcript-requester-name>Adrian Valerius</span>
                    </div>
                    {{-- Arrow --}}
                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                    </svg>
                    {{-- Architect --}}
                    <div class="flex items-center gap-2">
                        <img
                            src="https://ui-avatars.com/api/?name=Dr.+Elena+Thorne&background=ececec&color=333333&rounded=true&bold=true&size=32"
                            class="h-7 w-7 rounded-full border border-slate-200 object-cover"
                            alt="Dr. Elena Thorne"
                            data-transcript-architect-avatar
                        >
                        <span class="text-xs font-bold text-slate-700" data-transcript-architect-name>Dr. Elena Thorne</span>
                    </div>
                </div>
            </div>

            {{-- Chat body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5" data-transcript-body>
                {{-- Date divider --}}
                <div class="mb-6 flex justify-center">
                    <span class="rounded-full bg-[#FFF5EA] px-4 py-1 text-[10px] font-bold uppercase tracking-widest text-[#E8820C]" data-transcript-date>
                        October 24, 2023
                    </span>
                </div>

                {{-- Sample messages — will be replaced dynamically --}}
                <div class="space-y-5" data-transcript-messages>

                    {{-- Architect message (left-aligned) --}}
                    <div class="flex flex-col items-start">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dr. Elena Thorne</p>
                        <div class="max-w-[85%] rounded-2xl rounded-tl-md bg-white border border-slate-100 px-4 py-3 shadow-sm">
                            <p class="text-[13px] leading-relaxed text-slate-700">Good morning Adrian. I've reviewed the structural plans for the Meridian site. Before we dive in, can you confirm you're seeing the screen share clearly?</p>
                        </div>
                        <span class="mt-1.5 text-[10px] font-medium text-slate-300">09:02 AM</span>
                    </div>

                    {{-- Requester message (right-aligned) --}}
                    <div class="flex flex-col items-end">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Adrian Valerius <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#E8820C] align-middle ml-1"></span></p>
                        <div class="max-w-[85%] rounded-2xl rounded-tr-md bg-[#E8820C] px-4 py-3 shadow-sm">
                            <p class="text-[13px] leading-relaxed text-white">Actually, Elena, the connection seems very unstable. Your audio is cutting out and the screen share hasn't loaded at all on my end. I've tried refreshing twice.</p>
                        </div>
                        <span class="mt-1.5 text-[10px] font-medium text-slate-300">09:03 AM</span>
                    </div>

                    {{-- Architect message --}}
                    <div class="flex flex-col items-start">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dr. Elena Thorne</p>
                        <div class="max-w-[85%] rounded-2xl rounded-tl-md bg-white border border-slate-100 px-4 py-3 shadow-sm">
                            <p class="text-[13px] leading-relaxed text-slate-700">I see. Let me try toggling the low-bandwidth mode. Is it any better now? I'm showing a solid signal on my side.</p>
                        </div>
                        <span class="mt-1.5 text-[10px] font-medium text-slate-300">09:05 AM</span>
                    </div>

                    {{-- Requester message --}}
                    <div class="flex flex-col items-end">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Adrian Valerius <span class="inline-block h-1.5 w-1.5 rounded-full bg-[#E8820C] align-middle ml-1"></span></p>
                        <div class="max-w-[85%] rounded-2xl rounded-tr-md bg-[#E8820C] px-4 py-3 shadow-sm">
                            <p class="text-[13px] leading-relaxed text-white">No improvement. The lag is about 15 seconds. It's impossible to follow the technical walkthrough like this. Can we reschedule? I'm worried I'll be charged for a full session when we haven't even started.</p>
                        </div>
                        <span class="mt-1.5 text-[10px] font-medium text-slate-300">09:08 AM</span>
                    </div>

                    {{-- Architect message --}}
                    <div class="flex flex-col items-start">
                        <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Dr. Elena Thorne</p>
                        <div class="max-w-[85%] rounded-2xl rounded-tl-md bg-white border border-slate-100 px-4 py-3 shadow-sm">
                            <p class="text-[13px] leading-relaxed text-slate-700">I understand. Technical issues happen. I'll flag this to the platform support as a 'Technical Disconnect'. You shouldn't be penalized for this. Let's try again tomorrow at the same time.</p>
                        </div>
                        <span class="mt-1.5 text-[10px] font-medium text-slate-300">09:10 AM</span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [data-modal="transcript"]:not(.hidden) [data-modal-dialog] {
        animation: modalSlideUp 0.25s ease-out;
    }

    @keyframes modalSlideUp {
        from {
            opacity: 0;
            transform: translateY(24px) scale(0.97);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>
