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
    <div id="users-table-wrapper" data-users-url="{{ route('users.data') }}">
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
<script src="{{ asset('js/admin/pages/dashboard/users/index.js') }}"></script>
@endpush

@endsection
