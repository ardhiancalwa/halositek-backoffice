@extends('admin.layout.dashboard')

@section('title', 'Architect Management - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-slate-800 hover:text-[#E8820C] transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>

        <div class="relative w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-[#E8820C]" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            <input type="text" id="search-input" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E8820C]/20 focus:border-[#E8820C] sm:text-sm transition-all" placeholder="Search user" oninput="fetchAwards(1)">
        </div>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-3">
        <button class="px-5 py-2 rounded-full bg-[#E8820C] text-white text-sm font-semibold shadow-sm filter-btn" data-status="">
            All Status
        </button>
        <button class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors flex items-center gap-2 filter-btn" data-status="PENDING">
            Pending
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <button class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors flex items-center gap-2 filter-btn" data-status="APPROVED">
            Approved
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <button class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors flex items-center gap-2 filter-btn" data-status="DECLINED">
            Declined
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
    </div>

    <!-- Title and Dropdown -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-4">
            Architect Management
        </h2>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-md">+100</span>
            <button class="px-4 py-2 rounded-lg bg-[#FFF7EE] text-[#E8820C] border border-[#FFE8CC] text-sm font-bold uppercase tracking-wider flex items-center gap-2">
                Award
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <x-admin.list-table
        wrapper-class="bg-white border rounded-2xl border-slate-200 shadow-sm overflow-hidden"
        table-class="w-full text-left text-sm whitespace-nowrap"
        thead-class="bg-[#F8EADC] text-slate-600 font-bold uppercase tracking-[0.05em] text-[11px]"
        tbody-id="awards-table-body"
        tbody-class="divide-y divide-slate-100"
        :loading-colspan="7"
        loading-message="Loading awards data..."
    >
        <x-slot:head>
            <tr>
                <th class="px-6 py-4">Architect Name</th>
                <th class="px-6 py-4">Award Name</th>
                <th class="px-6 py-4">Awards Date</th>
                <th class="px-6 py-4">Submission Date</th>
                <th class="px-6 py-4">Award Status</th>
                <th class="px-6 py-4 text-center">Proof</th>
                <th class="px-6 py-4 text-center">Actions</th>
            </tr>
        </x-slot:head>
        <x-slot:pagination>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50" id="pagination-container"></div>
        </x-slot:pagination>
    </x-admin.list-table>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <!-- Card 1 -->
        <div class="bg-white rounded-2xl p-6 border border-[#E8820C] shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Award Quality Statictics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Pending</span>
                    <span class="text-[#E8820C] text-base" id="stat-award-pending">-</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Approved</span>
                    <span class="text-slate-400 text-base" id="stat-award-approved">-</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Declined</span>
                    <span class="text-red-500 text-base" id="stat-award-declined">-</span>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white rounded-2xl p-6 border border-[#E8820C] shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 mb-6">Design Quality Statictics</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Pending</span>
                    <span class="text-[#E8820C] text-base" id="stat-design-pending">-</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Approved</span>
                    <span class="text-slate-400 text-base" id="stat-design-approved">-</span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold">
                    <span class="text-slate-500 uppercase tracking-widest text-[11px]">Declined</span>
                    <span class="text-red-500 text-base" id="stat-design-declined">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentStatus = '';

    document.addEventListener('DOMContentLoaded', () => {
        setupFilters();
        fetchAwards();
        fetchStatistics();
    });

    function setupFilters() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-[#E8820C]', 'text-white', 'border-transparent');
                    b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                });
                
                const target = e.currentTarget;
                target.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
                target.classList.add('bg-[#E8820C]', 'text-white', 'border-transparent');

                currentStatus = target.dataset.status;
                fetchAwards(1);
            });
        });
    }

    async function fetchAwards(page = 1) {
        const search = document.getElementById('search-input').value;
        const tbody = document.getElementById('awards-table-body');
        
        let url = @js(route('architects.awards')) + `?page=${page}&per_page=10`;
        if (currentStatus) url += `&status=${currentStatus.toLowerCase()}`;
        if (search) url += `&search=${encodeURIComponent(search)}`;

        try {
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                const json = await res.json();
                renderTable(json.data);
                renderPagination(json.meta);
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load awards data.</td></tr>`;
            }
        } catch (error) {
            console.error('Error fetching awards:', error);
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">An error occurred.</td></tr>`;
        }
    }

    function renderTable(awards) {
        const tbody = document.getElementById('awards-table-body');
        
        if (awards.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-slate-500 font-medium">No awards found.</td></tr>`;
            return;
        }

        tbody.innerHTML = awards.map(item => {
            // Note: Adapting API properties conceptually. 
            // In API docs typical structure: name, project_name, status, architect (nested)
            const archName = item.architect?.name || item.architect_name || 'Julianne Vance';
            const awardName = item.name || 'Pritzker architecture...';
            
            const awardDateFormatted = item.award_date ? new Date(item.award_date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'Oct 24, 2023';
            const subDateFormatted = item.created_at ? new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'Oct 24, 2023';
            
            const rawStatus = (item.status || 'PENDING').toUpperCase();
            
            let statusBadge = '';
            if (rawStatus === 'PENDING') {
                statusBadge = `<span class="bg-orange-100 text-[#E8820C] text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">PENDING</span>`;
            } else if (rawStatus === 'APPROVED') {
                statusBadge = `<span class="bg-emerald-100 text-emerald-600 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">APPROVED</span>`;
            } else {
                statusBadge = `<span class="bg-red-100 text-red-600 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md text-nowrap">DECLINED</span>`;
            }

            return `
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-200 overflow-hidden flex-shrink-0">
                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(archName)}&background=F1F5F9&color=475569" alt="${archName}" class="w-full h-full object-cover">
                            </div>
                            <span class="font-bold text-slate-900">${archName}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-500 font-medium truncate max-w-[150px]">${awardName}</td>
                    <td class="px-6 py-4 text-slate-500 font-medium">
                        <span class="px-3 py-1.5 bg-slate-50 rounded-full border border-slate-100">${awardDateFormatted}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-500 font-medium">${subDateFormatted}</td>
                    <td class="px-6 py-4">${statusBadge}</td>
                    <td class="px-6 py-4 text-center">
                        <button class="text-[#E8820C] hover:text-orange-600 transition-colors inline-block" title="View Proof">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button class="text-[#778BA5] hover:text-[#E8820C] transition-colors p-1" title="View details">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderPagination(meta) {
        const container = document.getElementById('pagination-container');
        if (!meta || meta.total === 0) {
            container.innerHTML = '';
            return;
        }

        const currentPage = meta.current_page;
        const lastPage = meta.last_page;

        let html = `<div class="flex items-center gap-1 text-sm font-bold text-slate-500">`;

        // Prev btn
        html += `<button class="p-1 rounded hover:bg-slate-200 transition-colors ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === 1 ? 'disabled' : `onclick="fetchAwards(${currentPage - 1})"`}>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </button>`;

        // Pages
        for (let i = 1; i <= Math.min(3, lastPage); i++) {
            if (i === currentPage) {
                html += `<button class="w-7 h-7 rounded-md bg-[#E8820C] text-white flex items-center justify-center">${i}</button>`;
            } else {
                html += `<button class="w-7 h-7 rounded-md hover:bg-slate-200 transition-colors flex items-center justify-center" onclick="fetchAwards(${i})">${i}</button>`;
            }
        }
        
        if (lastPage > 4) {
            html += `<span class="px-1 text-slate-400">...</span>`;
            html += `<button class="w-7 h-7 rounded-md hover:bg-slate-200 flex items-center justify-center" onclick="fetchAwards(${lastPage})">${lastPage}</button>`;
        } else if (lastPage === 4) {
             html += `<button class="w-7 h-7 rounded-md hover:bg-slate-200 flex items-center justify-center ${currentPage === 4 ? 'bg-[#E8820C] text-white hover:bg-[#E8820C]' : ''}" onclick="fetchAwards(4)">4</button>`;
        }

        // Next btn
        html += `<button class="p-1 rounded hover:bg-slate-200 transition-colors ${currentPage === lastPage ? 'opacity-50 cursor-not-allowed' : ''}" ${currentPage === lastPage ? 'disabled' : `onclick="fetchAwards(${currentPage + 1})"`}>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
        </button>`;

        html += `</div>`;
        container.innerHTML = html;
    }

    async function fetchStatistics() {
        try {
            const response = await fetch(@js(route('architects.stats')), {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const awards = payload?.data?.awards ?? {};
            const designs = payload?.data?.designs ?? {};

            document.getElementById('stat-award-pending').textContent = String(awards.pending ?? 0);
            document.getElementById('stat-award-approved').textContent = String(awards.approved ?? 0);
            document.getElementById('stat-award-declined').textContent = String(awards.declined ?? 0);

            document.getElementById('stat-design-pending').textContent = String(designs.pending ?? 0);
            document.getElementById('stat-design-approved').textContent = String(designs.approved ?? 0);
            document.getElementById('stat-design-declined').textContent = String(designs.declined ?? 0);
        } catch (error) {
            console.error('Failed to load statistics', error);
        }
    }
</script>
@endpush
@endsection
