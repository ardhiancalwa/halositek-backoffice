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
    <div class="dashboard-filter-panel mb-6 p-6">
        <div class="flex items-center gap-3 flex-wrap">
            <button 
                data-status-filter="all"
                class="status-filter-btn dashboard-filter-button is-active text-sm font-semibold"
            >
                All Status
            </button>
            <button 
                data-status-filter="active"
                class="status-filter-btn dashboard-filter-button text-sm font-semibold"
            >
                Active
            </button>
            <button 
                data-status-filter="suspend"
                class="status-filter-btn dashboard-filter-button text-sm font-semibold"
            >
                Suspended
            </button>
        </div>
    </div>
    <!-- Table Section -->
    <div
        id="users-table-wrapper"
        data-users-url="{{ route('users.data') }}"
        data-user-update-url-template="{{ url('/api/v1/users/__ID__') }}"
    >
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

<x-admin.modal id="user-status-modal" title="Action" width-class="max-w-md">
    <div class="space-y-5">
        <div class="rounded-2xl border border-[#F2E6D4] bg-[#FFFBF5] p-4">
            <div class="flex items-center gap-3">
                <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-full bg-slate-200">
                    <img
                        id="user-status-modal-avatar"
                        src=""
                        alt=""
                        class="h-full w-full object-cover"
                    >
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500" id="user-status-modal-dot"></span>
                        <p id="user-status-modal-name" class="truncate text-base font-bold text-slate-900"></p>
                    </div>
                    <p id="user-status-modal-email" class="truncate text-sm font-medium text-slate-400"></p>
                </div>
            </div>
        </div>

        <div>
            <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-400">Information</p>
            <div class="space-y-2.5">
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                    <span class="font-semibold text-slate-900">Member since :</span>
                    <span id="user-status-modal-member-since"></span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                    <span class="font-semibold text-slate-900">Email :</span>
                    <span id="user-status-modal-email-detail"></span>
                </div>
            </div>
        </div>

        <form id="user-status-form" class="border-t border-slate-100 pt-4">
            <input type="hidden" id="user-status-id" name="user_id">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <label class="block">
                    <span class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-[#E8820C]">Account Status :</span>
                    <select
                        id="user-status-select"
                        name="account_status"
                        class="min-w-40 rounded-xl border border-[#F2C48B] bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-[#E8820C] focus:ring-4 focus:ring-orange-100"
                    >
                        <option value="active">ACTIVE</option>
                        <option value="suspend">SUSPENDED</option>
                    </select>
                </label>

                <div class="flex flex-col items-stretch gap-2 sm:items-end">
                    <p id="user-status-feedback" class="text-sm text-slate-500"></p>
                    <button
                        type="submit"
                        id="user-status-submit"
                        class="inline-flex items-center justify-center rounded-xl bg-[#E8820C] px-5 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white transition hover:bg-[#c8740b] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Update Status
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-admin.modal>

@push('scripts')
<script src="{{ asset('js/admin/pages/dashboard/users/index.js') }}?v={{ filemtime(public_path('js/admin/pages/dashboard/users/index.js')) }}"></script>
@endpush

@endsection
