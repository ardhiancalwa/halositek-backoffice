@extends('admin.layout.dashboard')

@section('title', 'User Management - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
    <!-- Header Section -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 mb-1 tracking-tight">User Management</h1>
            <p class="text-sm text-slate-500">Manage and monitor all registered users on the platform.</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm mb-6">
        <div class="flex items-center gap-3 flex-wrap">
            <button 
                data-status-filter="all"
                class="status-filter-btn px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-[#E8820C] text-white"
            >
                All Status
            </button>
            <button 
                data-status-filter="active"
                class="status-filter-btn px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-slate-100 text-slate-700 hover:bg-slate-200"
            >
                Active
            </button>
            <button 
                data-status-filter="suspend"
                class="status-filter-btn px-4 py-2 rounded-lg font-semibold text-sm transition-all bg-slate-100 text-slate-700 hover:bg-slate-200"
            >
                Suspended
            </button>
        </div>
    </div>
    <!-- Table Section -->
    <div id="users-table-wrapper">
        @component('admin.components.table', ['headers' => [
            ['label' => 'Name', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Email', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Member Since', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Account Status', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Actions', 'class' => 'text-center text-xs font-semibold uppercase tracking-wider text-slate-600'],
        ]])
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    <div class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </td>
            </tr>
        @endcomponent
    </div>

    <!-- Pagination -->
    <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between bg-white rounded-b-xl">
        <div class="text-sm text-slate-600">
            Showing <span id="current-page">1</span> of <span id="total-pages">1</span> pages
        </div>
        <div class="flex items-center gap-2">
            <button id="prev-page" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                &larr;
            </button>
            <div id="pagination-numbers" class="flex gap-1"></div>
            <button id="next-page" class="px-3 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                &rarr;
            </button>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    let currentPage = 1;
    let selectedStatus = 'all';
    const perPage = 15;

    const statusFilterBtns = document.querySelectorAll('.status-filter-btn');
    const tableBody = document.querySelector('#users-table-wrapper tbody');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const paginationNumbers = document.getElementById('pagination-numbers');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');

    // Helper function to format date
    function formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    // Helper function to get status badge
    function getStatusBadge(status) {
        if (status === 'active') {
            return '<span class="inline-flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-green-500"></span> ACTIVE</span>';
        } else if (status === 'suspend') {
            return '<span class="inline-flex items-center gap-2 text-xs font-semibold text-red-600"><span class="h-2 w-2 rounded-full bg-red-500"></span> SUSPENDED</span>';
        }
        return `<span class="text-xs font-semibold">${status?.toUpperCase()}</span>`;
    }

    // Fetch users data
    async function fetchUsers(page = 1, status = null) {
        let url = @js(route('users.data')) + `?page=${page}&per_page=${perPage}`;
        if (status && status !== 'all') {
            url += `&status=${status}`;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                },
            });
            if (!response.ok) return null;

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching users:', error);
            return null;
        }
    }

    // Render users table
    function renderUsers(data) {
        const users = data.data || [];
        
        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No users found</td></tr>';
            return;
        }

        tableBody.innerHTML = users.map(user => `
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="relative h-10 w-10 rounded-full overflow-hidden bg-slate-200">
                            <img 
                                src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=F1F5F9&color=475569&size=80" 
                                alt="${user.name}"
                                class="h-full w-full object-cover"
                            >
                        </div>
                        <span class="text-sm font-medium text-slate-900">${user.name}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${user.email}</td>
                <td class="px-6 py-4 text-sm text-slate-600">${formatDate(user.created_at)}</td>
                <td class="px-6 py-4">
                    ${getStatusBadge(user.account_status)}
                </td>
                <td class="px-6 py-4 text-center">
                    <button class="p-2 text-slate-400 hover:text-slate-600 transition" title="View user">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Render pagination
    function renderPagination(data) {
        const meta = data.meta || {};
        const totalPages = meta.last_page || 1;
        const currentPageNum = meta.current_page || 1;

        currentPageSpan.textContent = currentPageNum;
        totalPagesSpan.textContent = totalPages;

        // Disable/enable prev/next buttons
        prevPageBtn.disabled = currentPageNum === 1;
        nextPageBtn.disabled = currentPageNum === totalPages;

        // Generate page numbers
        paginationNumbers.innerHTML = '';
        for (let i = 1; i <= Math.min(totalPages, 5); i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-2 rounded-lg text-sm font-medium transition ${
                i === currentPageNum 
                    ? 'bg-[#E8820C] text-white' 
                    : 'border border-slate-200 text-slate-600 hover:bg-slate-50'
            }`;
            btn.onclick = () => loadPage(i);
            paginationNumbers.appendChild(btn);
        }
    }

    // Load page
    async function loadPage(page) {
        const data = await fetchUsers(page, selectedStatus);
        if (data) {
            renderUsers(data);
            renderPagination(data);
            currentPage = page;
        }
    }

    // Status filter handlers
    statusFilterBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            statusFilterBtns.forEach(b => {
                b.classList.remove('bg-[#E8820C]', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700');
            });
            e.target.classList.remove('bg-slate-100', 'text-slate-700');
            e.target.classList.add('bg-[#E8820C]', 'text-white');

            selectedStatus = e.target.dataset.statusFilter;
            currentPage = 1;
            await loadPage(1);
        });
    });

    // Pagination handlers
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) loadPage(currentPage - 1);
    });

    nextPageBtn.addEventListener('click', async () => {
        const data = await fetchUsers(currentPage + 1, selectedStatus);
        if (data && currentPage < (data.meta?.last_page || 1)) {
            loadPage(currentPage + 1);
        }
    });

    // Initial load
    await loadPage(1);
});
</script>
@endpush

@endsection

