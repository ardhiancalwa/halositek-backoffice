@php
    $archName = $item->architect->name ?? 'Unknown';
    $archPhoto = $item->architect && $item->architect->photo_profile 
        ? (str_starts_with($item->architect->photo_profile, 'http') ? $item->architect->photo_profile : Storage::url($item->architect->photo_profile))
        : null;

    $modalId = 'award-action-modal-' . $item->id;
    $rawStatus = strtoupper($item->status instanceof \BackedEnum ? $item->status->value : ($item->status ?? 'PENDING'));

    $proofUrl = $item->verification_file 
        ? (str_starts_with($item->verification_file, 'http') ? $item->verification_file : Storage::url($item->verification_file)) 
        : null;
@endphp

<div
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 hidden"
    data-award-action-modal="{{ $item->id }}"
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" data-modal-backdrop></div>

    <!-- Modal Dialog -->
    <div class="flex h-full items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col rounded-2xl bg-slate-50 shadow-2xl ring-1 ring-slate-900/5 transition-all text-slate-800" data-modal-dialog>
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 rounded-t-2xl">
                <h3 class="text-base font-black tracking-tight text-slate-900 uppercase">ACTION</h3>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    data-modal-close
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('admin.dashboard.architects.update-award-status', ['award' => $item]) }}" class="flex-1 overflow-y-auto w-full p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Information Section -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h4 class="text-[#E8820C] font-bold text-sm flex items-center gap-2 mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        Information
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Award Name</label>
                            <input type="text" value="{{ $item->name }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Award Date</label>
                            <input type="text" value="{{ $item->award_date ? $item->award_date->format('Y/m/d') : '-' }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Project Name</label>
                            <input type="text" value="{{ $item->project_name ?? '-' }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Role</label>
                            <input type="text" value="{{ $item->role ?? '-' }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Architect</label>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center overflow-hidden">
                                @if($archPhoto)
                                    <img src="{{ $archPhoto }}" alt="{{ $archName }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[#E8820C] text-xs font-bold">{{ substr($archName, 0, 2) }}</span>
                                @endif
                            </div>
                            <span class="font-bold text-slate-900 text-sm">{{ $archName }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Description</label>
                        <div class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-600 leading-relaxed min-h-[60px]">
                            {{ $item->description ?? 'No description provided.' }}
                        </div>
                    </div>
                </div>

                <!-- Proof Section -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[#E8820C] font-bold text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Verification Proof
                        </h4>
                        
                        @if($proofUrl)
                            <a href="{{ $proofUrl }}" target="_blank" class="text-xs font-bold text-[#E8820C] hover:text-[#D97706] hover:underline flex items-center gap-1 transition-colors">
                                View Full File
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        @endif
                    </div>
                    
                    @if($proofUrl)
                        @php
                            $isPdf = str_ends_with(strtolower($proofUrl), '.pdf');
                        @endphp
                        
                        <div class="w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center text-center p-2 min-h-[200px]">
                            @if($isPdf)
                                <div class="flex flex-col items-center justify-center p-6">
                                    <svg class="w-12 h-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-semibold text-slate-600">PDF Document attached</p>
                                    <p class="text-xs text-slate-500 mt-1">Click "View Full File" to download or preview.</p>
                                </div>
                            @else
                                <img src="{{ $proofUrl }}" class="max-w-full max-h-[400px] object-contain" alt="Verification File">
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-center text-slate-400 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                            <p class="text-sm font-medium">No verification file uploaded.</p>
                        </div>
                    @endif
                </div>

                <!-- Action Bar -->
                <div class="bg-white border-t border-slate-200 rounded-b-2xl p-6 sticky bottom-0 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)] mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="text-[11px] font-black uppercase tracking-widest text-[#E8820C]">Award Status :</label>
                        <select name="status" class="px-4 py-2 border border-slate-200 text-sm font-bold text-slate-700 rounded-lg bg-white focus:outline-none focus:border-[#E8820C] focus:ring-1 focus:ring-[#E8820C]">
                            <option value="pending" {{ $rawStatus === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="approved" {{ $rawStatus === 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                            <option value="declined" {{ $rawStatus === 'DECLINED' ? 'selected' : '' }}>DECLINED</option>
                        </select>
                    </div>

                    <button type="submit" class="bg-[#E8820C] hover:bg-[#D97706] text-white px-6 py-2.5 rounded-lg text-sm font-bold tracking-wide transition-colors">
                        UPDATE STATUS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [data-award-action-modal]:not(.hidden) [data-modal-dialog] {
        animation: awardModalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes awardModalPop {
        from { opacity: 0; transform: scale(0.96) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>