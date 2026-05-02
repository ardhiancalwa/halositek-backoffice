@php
    $images = $item->images;
    if (is_string($images)) {
        $images = json_decode($images, true) ?: [];
    }

    if (! is_array($images)) {
        $images = [];
    }

    $layouts = $item->layout_images ?? [];
    if (is_string($layouts)) {
        $layouts = json_decode($layouts, true) ?: [];
    }

    if (! is_array($layouts)) {
        $layouts = [];
    }

    $archName = $item->architect->name ?? 'Unknown';
    $archPhoto = $item->architect && $item->architect->photo_profile 
        ? Storage::url($item->architect->photo_profile) 
        : null;

    $modalId = 'design-action-modal-' . $item->id;
    $rawStatus = strtoupper($item->status instanceof \BackedEnum ? $item->status->value : ($item->status ?? 'PENDING'));
@endphp

<div
    id="{{ $modalId }}"
    class="fixed inset-0 z-50 hidden"
    data-design-action-modal="{{ $item->id }}"
>
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" data-modal-backdrop></div>

    <!-- Modal Dialog -->
    <div class="flex h-full items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-4xl max-h-[90vh] flex flex-col rounded-2xl bg-slate-50 shadow-2xl ring-1 ring-slate-900/5 transition-all text-slate-800" data-modal-dialog>
            
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
            <form method="POST" action="{{ route('admin.dashboard.architects.update-design-status', ['project' => $item]) }}" class="flex-1 overflow-y-auto w-full p-6 space-y-6">
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
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Project Title</label>
                            <input type="text" value="{{ $item->name }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Upload Date</label>
                            <input type="text" value="{{ $item->created_at ? $item->created_at->format('Y/m/d') : '-' }}" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 font-medium focus:outline-none">
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

                <!-- Specifications Section -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h4 class="text-[#E8820C] font-bold text-sm flex items-center gap-2 mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Specifications
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="border border-slate-200 rounded-lg p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Architectural Style</p>
                            <p class="text-sm font-semibold text-slate-900">{{ $item->style instanceof \BackedEnum ? ucfirst($item->style->value) : ucfirst($item->style ?? '-') }}</p>
                        </div>
                        <div class="border border-slate-200 rounded-lg p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Area</p>
                            <p class="text-sm font-semibold text-slate-900">{{ $item->area ?? '-' }}</p>
                        </div>
                        <div class="border border-slate-200 rounded-lg p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Highlight Features</p>
                            <p class="text-sm font-semibold text-slate-900">{{ $item->highlight_features ?? '-' }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Total Like</p>
                            <p class="text-sm font-bold text-slate-900">{{ number_format($item->likes_count ?? 0) }}</p>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-lg p-4 flex justify-between items-center bg-[#FFF8F0] border-[#FFE8CC]">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Estimated Cost</p>
                        <p class="text-sm font-bold text-[#E8820C]">{{ $item->estimated_cost ?? '-' }}</p>
                    </div>
                </div>

                <!-- Media Design Section -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h4 class="text-[#E8820C] font-bold text-sm flex items-center gap-2 mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Media Design
                    </h4>
                    
                    @if(count($images) > 0)
                        <div class="space-y-4">
                            @php
                                $mainImg = null;
                                $subImages = [];
                                foreach($images as $img) {
                                    if(empty($img)) continue;
                                    $url = str_starts_with($img, 'http') ? $img : Storage::url($img);
                                    if(!$mainImg) {
                                        $mainImg = $url;
                                    } else if(count($subImages) < 3) {
                                        $subImages[] = $url;
                                    }
                                }
                            @endphp
                            
                            @if($mainImg)
                                <div class="w-full h-48 rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
                                    <img src="{{ $mainImg }}" class="w-full h-full object-cover" alt="Main Design">
                                </div>
                            @endif

                            @if(count($subImages) > 0)
                                <div class="grid grid-cols-3 gap-4">
                                    @foreach($subImages as $subImg)
                                        <div class="aspect-video rounded-lg overflow-hidden border border-slate-200 bg-slate-100">
                                            <img src="{{ $subImg }}" class="w-full h-full object-cover" alt="Design view">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-center text-slate-400 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                            <p class="text-sm font-medium">No media uploaded.</p>
                        </div>
                    @endif
                </div>

                <!-- Layout Design Section -->
                <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                    <h4 class="text-[#E8820C] font-bold text-sm flex items-center gap-2 mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Layout Design
                    </h4>

                    @if(count($layouts) > 0)
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(array_slice($layouts, 0, 3) as $lyt)
                                @php
                                    if(empty($lyt)) continue;
                                    $url = str_starts_with($lyt, 'http') ? $lyt : Storage::url($lyt);
                                @endphp
                                <div class="aspect-square rounded-lg overflow-hidden border border-slate-200 bg-slate-100 p-2">
                                    <img src="{{ $url }}" class="w-full h-full object-cover" alt="Layout view">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-10 text-center text-slate-400 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                            <p class="text-sm font-medium">No layout uploaded.</p>
                        </div>
                    @endif
                </div>

                <!-- Action Bar -->
                <div class="bg-white border-t border-slate-200 rounded-b-2xl p-6 sticky bottom-0 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.05)] mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <label class="text-[11px] font-black uppercase tracking-widest text-[#E8820C]">Design Status :</label>
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
    [data-design-action-modal]:not(.hidden) [data-modal-dialog] {
        animation: designModalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes designModalPop {
        from { opacity: 0; transform: scale(0.96) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>