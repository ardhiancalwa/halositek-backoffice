@extends('admin.layout.dashboard')

@section('title', 'Consultations - HaloSitek')

@section('content')
<div id="consultations-wrapper"
     class="max-w-7xl mx-auto pb-12"
     data-report-url="{{ route('admin.dashboard.consultations.report-data') }}"
     data-payroll-url="{{ route('admin.dashboard.consultations.payroll-data') }}"
     data-report-stats-url="{{ route('admin.dashboard.consultations.report-stats') }}"
     data-payroll-summary-url="{{ route('admin.dashboard.consultations.payroll-summary') }}"
     data-architect-consultations-url="{{ route('admin.dashboard.consultations.architect-consultations', ['architectId' => ':id']) }}"
     data-release-payroll-url="{{ route('admin.dashboard.consultations.release-payroll', ['architectId' => ':id']) }}"
     data-transcript-url="{{ route('admin.dashboard.consultations.transcript', ['consultation' => ':id']) }}"
     data-report-status-url="{{ route('admin.dashboard.consultations.report-status', ['report' => ':id']) }}"
>
    <!-- Toggle Buttons (Report / Payroll) -->
    <div class="mb-6 block">
        <div class="inline-flex bg-white rounded-xl border-2 border-[#E8820C] overflow-hidden p-0.5">
            <button
                id="tabBtnReport"
                class="px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer bg-[#E8820C] text-white shadow-sm"
                onclick="switchTab('report')"
            >Report</button>
            <button
                id="tabBtnPayroll"
                class="px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer bg-white text-[#E8820C] hover:bg-orange-50"
                onclick="switchTab('payroll')"
            >Payroll</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- REPORT TAB                                                  -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div id="tabReport">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Real-time Statictics</h1>

        <!-- Stat Cards (skeleton → loaded by JS) -->
        <div id="report-stats-grid" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            @php
                $statKeys = [
                    ['key' => 'total_report', 'title' => 'Total Report'],
                    ['key' => 'new_report', 'title' => 'New Report'],
                    ['key' => 'user_report', 'title' => 'User Report'],
                    ['key' => 'architect_report', 'title' => 'Architect Report'],
                ];
            @endphp
            @foreach($statKeys as $stat)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_20px_-5px_rgba(0,0,0,0.05)] flex flex-col h-[140px]">
                <p class="text-[11px] text-slate-500 font-bold tracking-wider mb-3 uppercase">{{ $stat['title'] }}</p>
                <div id="stat-{{ $stat['key'] }}" class="mb-auto">
                    {{-- Skeleton --}}
                    <div class="skeleton-pulse h-10 w-24 rounded-lg bg-slate-200"></div>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden w-full">
                    <div id="stat-bar-{{ $stat['key'] }}" class="h-full bg-[#E8820C] rounded-full transition-all duration-700 ease-out" style="width: 0%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filters Row -->
        <div class="flex items-center gap-3 mb-6">
            <button data-filter="all" class="report-filter-btn bg-[#E8820C] text-white px-8 py-2.5 rounded-full text-sm font-bold shadow-[0_4px_14px_0_rgba(232,130,12,0.39)] cursor-pointer transition-colors" data-active="true">All Report</button>

            <button data-filter="user" class="report-filter-btn bg-white text-slate-600 px-6 py-2.5 rounded-full text-sm font-bold border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors cursor-pointer">
                User
            </button>

            <button data-filter="architect" class="report-filter-btn bg-white text-slate-600 px-6 py-2.5 rounded-full text-sm font-bold border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors cursor-pointer">
                Architect
            </button>
        </div>

        <!-- Report Table -->
        @component('admin.components.table', ['headers' => [
            'REQUESTER',
            'REASON',
            ['label' => 'CONSULTATION DATE', 'class' => 'text-center'],
            ['label' => 'OPPOSING PARTY', 'class' => 'text-center'],
            ['label' => 'NOMINAL (Rp)', 'class' => 'text-center'],
            ['label' => 'TRANSCRIPT', 'class' => 'text-center'],
            ['label' => 'ACTION PAYMENT', 'class' => 'text-center']
        ], 'tbodyId' => 'report-table-body'])
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    <div class="flex items-center justify-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium text-slate-400">Loading data...</span>
                    </div>
                </td>
            </tr>
        @endcomponent

        <!-- Report Pagination -->
        @component('admin.components.pagination-footer', [
            'currentPage' => 1,
            'totalPages' => 1,
            'previousDisabled' => true,
            'nextDisabled' => false,
            'currentPageId' => 'report-current-page',
            'totalPagesId' => 'report-total-pages',
            'prevButtonId' => 'report-prev-page',
            'nextButtonId' => 'report-next-page',
            'numbersId' => 'report-pagination-numbers',
        ])
        @endcomponent
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- PAYROLL TAB                                                 -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div id="tabPayroll" class="hidden">
        <!-- Pending Payouts Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Pending Payouts</h1>
            <div id="payroll-pending-amount">
                {{-- Skeleton --}}
                <div class="skeleton-pulse h-12 w-72 rounded-lg bg-slate-200"></div>
            </div>
        </div>

        <!-- Payout Queue Header + Filter -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-slate-900">Payout Queue</h3>

            @include('admin.components.consultations.payroll-filter-dropdown', [
                'id' => 'payrollSubFilter',
                'name' => 'payrollSubFilter',
                'value' => 'all',
                'onChange' => 'switchPayrollSub(this.value)'
            ])
        </div>

        <!-- ALL Filter: Single combined table -->
        <div id="payrollSubAll">
            @component('admin.components.table', ['headers' => [
                'ARCHITECT NAME',
                ['label' => 'TOTAL EARNINGS (Rp)', 'class' => 'text-center'],
                ['label' => 'PER SESSION (Rp)', 'class' => 'text-center'],
                ['label' => 'TOTAL CONSULTATION', 'class' => 'text-center'],
                ['label' => 'ACTION', 'class' => 'text-center']
            ], 'tbodyId' => 'payroll-table-body-all'])
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-slate-400">Loading data...</span>
                        </div>
                    </td>
                </tr>
            @endcomponent

            @component('admin.components.pagination-footer', [
                'currentPage' => 1,
                'totalPages' => 1,
                'previousDisabled' => true,
                'nextDisabled' => false,
                'currentPageId' => 'payroll-all-current-page',
                'totalPagesId' => 'payroll-all-total-pages',
                'prevButtonId' => 'payroll-all-prev-page',
                'nextButtonId' => 'payroll-all-next-page',
                'numbersId' => 'payroll-all-pagination-numbers',
            ])
            @endcomponent
        </div>

        <!-- PAY Sub-tab: Table with Release Payment action -->
        <div id="payrollSubPay" class="hidden">
            @component('admin.components.table', ['headers' => [
                'ARCHITECT NAME',
                ['label' => 'TOTAL EARNINGS (Rp)', 'class' => 'text-center'],
                ['label' => 'PER SESSION (Rp)', 'class' => 'text-center'],
                ['label' => 'TOTAL CONSULTATION', 'class' => 'text-center'],
                ['label' => 'ACTION', 'class' => 'text-center']
            ], 'tbodyId' => 'payroll-table-body-pay'])
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-slate-400">Loading data...</span>
                        </div>
                    </td>
                </tr>
            @endcomponent

            @component('admin.components.pagination-footer', [
                'currentPage' => 1,
                'totalPages' => 1,
                'previousDisabled' => true,
                'nextDisabled' => false,
                'currentPageId' => 'payroll-pay-current-page',
                'totalPagesId' => 'payroll-pay-total-pages',
                'prevButtonId' => 'payroll-pay-prev-page',
                'nextButtonId' => 'payroll-pay-next-page',
                'numbersId' => 'payroll-pay-pagination-numbers',
            ])
            @endcomponent
        </div>

        <!-- HISTORY Sub-tab: Table with Selesai action -->
        <div id="payrollSubHistory" class="hidden">
            @component('admin.components.table', ['headers' => [
                'ARCHITECT NAME',
                ['label' => 'TOTAL EARNINGS (Rp)', 'class' => 'text-center'],
                ['label' => 'PER SESSION (Rp)', 'class' => 'text-center'],
                ['label' => 'TOTAL CONSULTATION', 'class' => 'text-center'],
                ['label' => 'ACTION', 'class' => 'text-center']
            ], 'tbodyId' => 'payroll-table-body-history'])
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-slate-400">Loading data...</span>
                        </div>
                    </td>
                </tr>
            @endcomponent

            @component('admin.components.pagination-footer', [
                'currentPage' => 1,
                'totalPages' => 1,
                'previousDisabled' => true,
                'nextDisabled' => false,
                'currentPageId' => 'payroll-history-current-page',
                'totalPagesId' => 'payroll-history-total-pages',
                'prevButtonId' => 'payroll-history-prev-page',
                'nextButtonId' => 'payroll-history-next-page',
                'numbersId' => 'payroll-history-pagination-numbers',
            ])
            @endcomponent
        </div>
    </div>
</div>

{{-- Include Modal Components --}}
@include('admin.components.consultations.modal-transcript')
@include('admin.components.consultations.modal-payment')
@include('admin.components.consultations.modal-release')
@include('admin.components.consultations.modal-selesai')

@push('scripts')
<script src="{{ asset('js/admin/pages/dashboard/consultations/index.js') }}?v={{ filemtime(public_path('js/admin/pages/dashboard/consultations/index.js')) }}"></script>
@endpush
@endsection
