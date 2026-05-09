@extends('admin.layout.dashboard')

@section('title', 'Admin Management - HaloSitek')

@section('content')
<div class="dashboard-page-shell">
    
    <!-- Top Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="h-8 w-8 rounded-full bg-orange-50 flex items-center justify-center text-[#E8820C]">
                    <img src="{{ asset('images/dashboard/admin-icon-orange.svg') }}" alt="Registered Admin" class="h-5 w-5">
                </div>
            </div>
            <p class="text-sm font-medium text-slate-500 mb-1">Registered Admin</p>
            <h3 class="text-3xl font-bold text-slate-900" id="stat-registered-admin">{{ $registeredAdminCount }}</h3>
        </div>
        
        <div class="bg-white p-6 rounded-2xl border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="h-8 w-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                    <img src="{{ asset('images/dashboard/admin-icon-green.svg') }}" alt="Active Admin" class="h-5 w-5">
                </div>
            </div>
            <p class="text-sm font-medium text-slate-500 mb-1">Active Admin</p>
            <h3 class="text-3xl font-bold text-slate-900" id="stat-active-admin">{{ $activeAdminCount }}</h3>
        </div>
    </div>

    <!-- Header Section -->
    <div class="dashboard-page-header flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="dashboard-section-title mb-1 text-2xl font-bold tracking-tight">Super Admin Console</h1>
            <p class="dashboard-section-subtitle text-sm">Administrator Management & System Governance</p>
        </div>
        
        <button id="add-admin-btn" class="inline-flex items-center gap-2 bg-[#E8820C] hover:bg-[#d4760a] text-white px-6 py-3 rounded-lg text-sm font-bold shadow-sm transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Admin
        </button>
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
        data-users-url="{{ route('admin.dashboard.admins.data') }}"
        data-user-update-url-template="{{ url('/dashboard/admins/__ID__') }}"
    >
        @component('admin.components.table', ['headers' => [
            ['label' => 'Name', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Email', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
            ['label' => 'Password', 'class' => 'text-left text-xs font-semibold uppercase tracking-wider text-slate-600'],
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
    @component('admin.components.pagination-footer', [
        'currentPage' => 1,
        'totalPages' => 1,
        'previousDisabled' => true,
        'nextDisabled' => false,
        'currentPageId' => 'current-page',
        'totalPagesId' => 'total-pages',
        'prevButtonId' => 'prev-page',
        'nextButtonId' => 'next-page',
        'numbersId' => 'pagination-numbers',
    ])
    @endcomponent
</div>

@component('admin.components.modal', ['id' => 'user-status-modal', 'title' => 'Action', 'widthClass' => 'max-w-md'])
    <div class="space-y-5">
        <div class="dashboard-modal-highlight flex justify-center">
            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full bg-slate-900 border-4 border-white shadow-sm flex items-center justify-center">
                <img
                    id="user-status-modal-avatar"
                    src=""
                    alt=""
                    class="h-full w-full object-cover"
                >
            </div>
        </div>

        <form id="user-status-form">
            <input type="hidden" id="user-status-id" name="user_id">
            
            <p class="dashboard-modal-section-label mb-3 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Information</p>
            <div class="space-y-3 mb-6">
                <!-- Name -->
                <div class="flex items-center gap-3">
                    <label for="edit_admin_name" class="w-20 text-sm font-bold text-slate-900">Name <span class="float-right">:</span></label>
                    <input 
                        type="text" 
                        id="edit_admin_name" 
                        name="name" 
                        class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                    >
                </div>

                <!-- Password -->
                <div class="flex items-center gap-3">
                    <label for="edit_admin_password" class="w-20 text-sm font-bold text-slate-900">Password <span class="float-right">:</span></label>
                    <input 
                        type="password" 
                        id="edit_admin_password" 
                        name="password" 
                        placeholder="Password"
                        class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                    >
                </div>

                <!-- Email -->
                <div class="flex items-center gap-3">
                    <label for="edit_admin_email" class="w-20 text-sm font-bold text-slate-900">Email <span class="float-right">:</span></label>
                    <input 
                        type="email" 
                        id="edit_admin_email" 
                        name="email" 
                        class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-2.5 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                    >
                </div>
            </div>

            <div class="dashboard-modal-divider">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#E8820C]">User Status :</span>
                        <select
                            id="user-status-select"
                            name="account_status"
                            class="dashboard-modal-select text-sm font-bold rounded-lg border border-[#E8820C] text-[#0A1A3B] focus:ring-[#E8820C] focus:border-[#E8820C]"
                        >
                            <option value="active">ACTIVE</option>
                            <option value="suspend">SUSPENDED</option>
                        </select>
                    </div>

                    <div class="flex flex-col items-stretch gap-2 sm:items-end">
                        <p id="user-status-feedback" class="text-sm text-slate-500"></p>
                        <button
                            type="button"
                            id="btn-trigger-update"
                            class="bg-[#E8820C] hover:bg-[#d4760a] text-white px-6 py-2.5 rounded-lg text-sm font-bold uppercase tracking-[0.08em] shadow-sm transition-colors"
                        >
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endcomponent

@component('admin.components.modal', ['id' => 'add-admin-modal', 'title' => 'Add New Administrator', 'widthClass' => 'max-w-2xl'])
<div class="space-y-5">
    <p class="dashboard-modal-section-label -mt-3 mb-6 text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">CREDENTIALS & ACCESS CONTROL</p>
    
    <form id="add-admin-form" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Full Name -->
            <div>
                <label for="admin_name" class="dashboard-modal-select-label mb-2 block text-[11px] font-bold uppercase tracking-[0.18em]">Full Name</label>
                <input 
                    type="text" 
                    id="admin_name" 
                    name="name" 
                    required 
                    placeholder="e.g. Eleanor Vance" 
                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                >
            </div>

            <!-- Email Address -->
            <div>
                <label for="admin_email" class="dashboard-modal-select-label mb-2 block text-[11px] font-bold uppercase tracking-[0.18em]">Email Address</label>
                <input 
                    type="email" 
                    id="admin_email" 
                    name="email" 
                    required 
                    placeholder="e.g. e.vance@halositek.com" 
                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                >
            </div>

            <!-- Password -->
            <div class="sm:col-span-2">
                <label for="admin_password" class="dashboard-modal-select-label mb-2 block text-[11px] font-bold uppercase tracking-[0.18em]">Password</label>
                <input 
                    type="password" 
                    id="admin_password" 
                    name="password" 
                    required 
                    placeholder="••••••••" 
                    class="block w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-900 transition-colors focus:border-[#E8820C] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#E8820C]"
                >
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 mt-2">
            <button 
                type="button" 
                data-modal-close 
                class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
            >
                Cancel
            </button>
            <button 
                type="button" 
                id="btn-provision-admin"
                class="bg-[#E8820C] hover:bg-[#d4760a] text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors"
            >
                Provision Administrator
            </button>
        </div>
    </form>
</div>
@endcomponent

@component('admin.components.modal', ['id' => 'confirm-admin-modal', 'title' => '', 'widthClass' => 'max-w-md'])
<div class="text-center px-4 py-2 pb-6">
    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-orange-50 text-[#E8820C]">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
    </div>
    <h3 class="mb-2 text-2xl font-black text-[#0A1A3B]">Confirm add</h3>
    <p class="mb-8 text-base text-slate-500">Are you sure you want to add new admin<br>to manage ?</p>
    
    <div class="flex flex-col gap-3">
        <button 
            type="button" 
            id="btn-confirm-add"
            class="w-full bg-[#E8820C] hover:bg-[#d4760a] text-white px-6 py-3.5 rounded-lg text-base font-bold shadow-sm transition-colors"
        >
            Yes
        </button>
        <button 
            type="button" 
            onclick="document.getElementById('confirm-admin-modal').classList.add('hidden'); document.getElementById('confirm-admin-modal').classList.remove('flex');"
            class="w-full bg-slate-50 hover:bg-slate-100 text-[#334155] px-6 py-3.5 rounded-lg text-base font-bold transition-colors"
        >
            Cancel
        </button>
    </div>
</div>
@endcomponent

@component('admin.components.modal', ['id' => 'confirm-update-modal', 'title' => '', 'widthClass' => 'max-w-md'])
<div class="text-center px-4 py-2 pb-6">
    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-orange-50 text-[#E8820C]">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <h3 class="mb-2 text-2xl font-black text-[#0A1A3B]">Confirm Changes</h3>
    <p class="mb-8 text-base text-slate-500">Are you sure you want to save the<br>changes you made to the admin<br>information ?</p>
    
    <div class="flex flex-col gap-3">
        <button 
            type="button" 
            id="btn-confirm-update"
            class="w-full bg-[#E8820C] hover:bg-[#d4760a] text-white px-6 py-3.5 rounded-lg text-base font-bold shadow-sm transition-colors"
        >
            Save Changes
        </button>
        <button 
            type="button" 
            onclick="document.getElementById('confirm-update-modal').classList.add('hidden'); document.getElementById('confirm-update-modal').classList.remove('flex');"
            class="w-full bg-slate-50 hover:bg-slate-100 text-[#334155] px-6 py-3.5 rounded-lg text-base font-bold transition-colors"
        >
            Cancel
        </button>
    </div>
</div>
@endcomponent

@push('scripts')
<script src="{{ asset('js/admin/pages/dashboard/admins/index.js') }}?v={{ filemtime(public_path('js/admin/pages/dashboard/admins/index.js')) }}"></script>
@endpush

@endsection
