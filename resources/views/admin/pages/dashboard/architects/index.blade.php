@extends('admin.layout.dashboard')

@section('title', 'Architect Management - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Award Quality Statistics Card -->
        <div class="bg-white rounded-2xl p-6 border border-[#E8820C] shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Award Quality Statictics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Pending</span>
                    <span class="text-[#E8820C] text-base">{{ $awardStats['pending'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Approved</span>
                    <span class="text-slate-400 text-base">{{ $awardStats['approved'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Declined</span>
                    <span class="text-red-500 text-base">{{ $awardStats['declined'] }}</span>
                </div>
            </div>
        </div>

        <!-- Design Quality Statistics Card -->
        <div class="bg-white rounded-2xl p-6 border border-[#E8820C] shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Design Quality Statictics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Pending</span>
                    <span class="text-[#E8820C] text-base">{{ $designStats['pending'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Approved</span>
                    <span class="text-slate-400 text-base">{{ $designStats['approved'] }}</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Declined</span>
                    <span class="text-red-500 text-base">{{ $designStats['declined'] }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Filters -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard.architects.index', ['type' => $type, 'status' => '']) }}"
           class="px-5 py-2 rounded-full text-sm font-semibold shadow-sm transition-colors
                  {{ $status === '' ? 'bg-[#E8820C] text-white' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
            All Status
        </a>
        <a href="{{ route('admin.dashboard.architects.index', ['type' => $type, 'status' => 'pending']) }}"
           class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                  {{ $status === 'pending' ? 'bg-[#E8820C] text-white border border-transparent' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
            Pending
        </a>
        <a href="{{ route('admin.dashboard.architects.index', ['type' => $type, 'status' => 'approved']) }}"
           class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                  {{ $status === 'approved' ? 'bg-[#E8820C] text-white border border-transparent' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
            Approved
        </a>
        <a href="{{ route('admin.dashboard.architects.index', ['type' => $type, 'status' => 'declined']) }}"
           class="px-4 py-2 rounded-full text-sm font-medium transition-colors
                  {{ $status === 'declined' ? 'bg-[#E8820C] text-white border border-transparent' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
            Declined
        </a>
    </div>

    <!-- Title and Dropdown -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-4">
            Architect Management
        </h2>
        <div class="flex items-center gap-2 relative" id="type-dropdown-wrapper">
            <span class="px-2.5 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-md">+{{ $items->total() }}</span>
            <button type="button" id="type-dropdown-btn"
                    class="px-4 py-2 rounded-lg bg-[#FFF7EE] text-[#E8820C] border border-[#FFE8CC] text-sm font-bold uppercase tracking-wider flex items-center gap-2 cursor-pointer">
                {{ $type === 'design' ? 'Design' : 'Award' }}
                <svg class="w-4 h-4 transition-transform" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <!-- Dropdown Menu -->
            <div id="type-dropdown-menu" class="absolute right-0 top-full mt-1 w-40 bg-white rounded-lg shadow-lg border border-slate-100 overflow-hidden z-50 hidden">
                <a href="{{ route('admin.dashboard.architects.index', ['type' => 'award', 'status' => '']) }}"
                   class="block px-4 py-2.5 text-sm font-semibold transition-colors {{ $type === 'award' ? 'bg-[#FFF7EE] text-[#E8820C]' : 'text-slate-700 hover:bg-slate-50' }}">
                    Award
                </a>
                <a href="{{ route('admin.dashboard.architects.index', ['type' => 'design', 'status' => '']) }}"
                   class="block px-4 py-2.5 text-sm font-semibold transition-colors {{ $type === 'design' ? 'bg-[#FFF7EE] text-[#E8820C]' : 'text-slate-700 hover:bg-slate-50' }}">
                    Design
                </a>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto w-full rounded-xl bg-white shadow-sm border border-slate-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-[#FDFBF7] text-slate-600 text-[11px] font-bold uppercase tracking-wider">
                    <th class="py-4 px-6">Architect Name</th>
                    @if($type === 'design')
                        <th class="py-4 px-6">Design Title</th>
                        <th class="py-4 px-6">Style</th>
                    @else
                        <th class="py-4 px-6">Award Name</th>
                        <th class="py-4 px-6">Awards Date</th>
                    @endif
                    <th class="py-4 px-6">Submission Date</th>
                    <th class="py-4 px-6">{{ $type === 'design' ? 'Design Status' : 'Award Status' }}</th>
                    @if($type !== 'design')
                        <th class="py-4 px-6 text-center">Proof</th>
                    @endif
                    <th class="py-4 px-6 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($items as $item)
                    @php
                        $archName = $item->architect->name ?? 'Unknown';
                        $rawStatus = strtoupper($item->status instanceof \BackedEnum ? $item->status->value : ($item->status ?? 'pending'));
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($item->architect && $item->architect->photo_profile)
                                        <img src="{{ Storage::url($item->architect->photo_profile) }}" alt="{{ $archName }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                    @endif
                                </div>
                                <span class="font-bold text-slate-900">{{ $archName }}</span>
                            </div>
                        </td>

                        @if($type === 'design')
                            <td class="px-6 py-4 text-slate-500 font-medium truncate max-w-[150px]">{{ $item->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 bg-slate-50 rounded-full border border-slate-100 text-slate-600 text-sm font-medium">
                                    {{ $item->style instanceof \BackedEnum ? ucfirst($item->style->value) : ucfirst($item->style ?? '-') }}
                                </span>
                            </td>
                        @else
                            <td class="px-6 py-4 text-slate-500 font-medium truncate max-w-[150px]">{{ $item->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                <span class="px-3 py-1.5 bg-slate-50 rounded-full border border-slate-100">
                                    {{ $item->award_date ? $item->award_date->format('M d, Y') : '-' }}
                                </span>
                            </td>
                        @endif

                        <td class="px-6 py-4 text-slate-500 font-medium">
                            {{ $item->created_at ? $item->created_at->format('M d, Y') : '-' }}
                        </td>

                        <td class="px-6 py-4">
                            @if($rawStatus === 'PENDING')
                                <span class="bg-orange-100 text-[#E8820C] text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">PENDING</span>
                            @elseif($rawStatus === 'APPROVED')
                                <span class="bg-emerald-100 text-emerald-600 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">APPROVED</span>
                            @elseif($rawStatus === 'DECLINED')
                                <span class="bg-red-100 text-red-600 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">DECLINED</span>
                            @endif
                        </td>

                        @if($type !== 'design')
                            <td class="px-6 py-4 text-center">
                                <button class="text-[#E8820C] hover:text-orange-600 transition-colors inline-block" title="View Proof">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </button>
                            </td>
                        @endif

                        <td class="px-6 py-4 text-center">
                            @if($type === 'design')
                                <button type="button" class="text-[#778BA5] hover:text-[#E8820C] transition-colors p-1" title="View details" data-open-design-modal="{{ $item->id }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                @include('admin.components.architects.modal-design-action', ['item' => $item])
                            @else
                                <button type="button" class="text-[#778BA5] hover:text-[#E8820C] transition-colors p-1" title="View details">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $type === 'design' ? 6 : 7 }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-slate-400 font-medium text-sm">
                                    No {{ $type === 'design' ? 'designs' : 'awards' }} found
                                    @if($status)
                                        with status "{{ ucfirst($status) }}"
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end bg-white">
                <div class="flex items-center gap-1 text-sm font-bold text-slate-500">
                    {{-- Previous Button --}}
                    @if($items->onFirstPage())
                        <span class="p-1.5 rounded opacity-50 cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </span>
                    @else
                        <a href="{{ $items->previousPageUrl() }}" class="p-1.5 rounded hover:bg-slate-100 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $items->currentPage();
                        $lastPage = $items->lastPage();
                        $pages = [];

                        // Always show first 3 pages
                        for ($i = 1; $i <= min(3, $lastPage); $i++) {
                            $pages[] = $i;
                        }

                        // Show last page if there's a gap
                        if ($lastPage > 4) {
                            $pages[] = '...';
                            $pages[] = $lastPage;
                        } elseif ($lastPage === 4) {
                            $pages[] = 4;
                        }
                    @endphp

                    @foreach($pages as $page)
                        @if($page === '...')
                            <span class="px-1 text-slate-400">...</span>
                        @elseif($page == $currentPage)
                            <span class="w-8 h-8 rounded-md bg-[#C5923A] text-white flex items-center justify-center text-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $items->url($page) }}" class="w-8 h-8 rounded-md hover:bg-slate-100 transition-colors flex items-center justify-center text-sm">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Button --}}
                    @if($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}" class="p-1.5 rounded hover:bg-slate-100 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @else
                        <span class="p-1.5 rounded opacity-50 cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropdownBtn = document.getElementById('type-dropdown-btn');
        const dropdownMenu = document.getElementById('type-dropdown-menu');
        const dropdownArrow = document.getElementById('dropdown-arrow');

        if (dropdownBtn && dropdownMenu) {
            dropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownMenu.classList.toggle('hidden');
                dropdownArrow.classList.toggle('rotate-180');
            });

            document.addEventListener('click', (e) => {
                if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                    dropdownArrow.classList.remove('rotate-180');
                }
            });
        }

        // Modal Design Action functionality
        const openButtons = document.querySelectorAll('[data-open-design-modal]');
        openButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-open-design-modal');
                const modal = document.querySelector(`[data-design-action-modal="${id}"]`);
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        const closeBtns = document.querySelectorAll('[data-design-action-modal] [data-modal-close], [data-design-action-modal] [data-modal-backdrop]');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('[data-design-action-modal]');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    });
</script>
@endpush
@endsection
