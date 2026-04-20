@extends('admin.layout.dashboard')

@section('title', 'Consultations - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
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

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            @php
                $stats = [
                    ['title' => 'Total Report', 'value' => '420', 'progress' => '40'],
                    ['title' => 'New Report', 'value' => '56', 'progress' => '20'],
                    ['title' => 'User Report', 'value' => '40', 'progress' => '80'],
                    ['title' => 'Architect Report', 'value' => '16', 'progress' => '15'],
                ];
            @endphp
            @foreach($stats as $stat)
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-[0_4px_20px_-5px_rgba(0,0,0,0.05)] flex flex-col h-[140px]">
                <p class="text-[11px] text-slate-500 font-bold tracking-wider mb-3 uppercase">{{ $stat['title'] }}</p>
                <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-auto">{{ $stat['value'] }}</h3>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden w-full">
                    <div class="h-full bg-[#E8820C] rounded-full" style="width: {{ $stat['progress'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Filters Row -->
        <div class="flex items-center gap-3 mb-6">
            <button class="bg-[#E8820C] text-white px-8 py-2.5 rounded-full text-sm font-bold shadow-[0_4px_14px_0_rgba(232,130,12,0.39)] cursor-pointer">All Report</button>
            
            <div class="relative">
                <button class="bg-white text-slate-600 px-6 py-2.5 rounded-full text-sm font-bold border border-slate-200 shadow-sm flex items-center gap-3 hover:bg-slate-50 transition-colors cursor-pointer">
                    User 
                </button>
            </div>

            <div class="relative">
                <button class="bg-white text-slate-600 px-6 py-2.5 rounded-full text-sm font-bold border border-slate-200 shadow-sm flex items-center gap-3 hover:bg-slate-50 transition-colors cursor-pointer">
                    Architect 
                </button>
            </div>
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
        ]])
            @foreach(range(1, 4) as $item)
            <tr class="group hover:bg-slate-50 transition-colors">
                <td class="py-5 px-6 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Adrian+Valerius&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                        <div class="flex flex-col">
                            <span class="text-sm font-extrabold text-slate-900">Adrian<br>Valerius</span>
                            <span class="text-[9px] font-black bg-slate-100/80 text-slate-500 px-2 py-0.5 rounded uppercase w-max mt-1 tracking-wider">USER</span>
                        </div>
                    </div>
                </td>
                <td class="py-5 px-6 text-sm text-slate-500 font-medium">Technical connectivity is...</td>
                <td class="py-5 px-6 text-sm text-slate-500 font-medium text-center whitespace-nowrap">Oct 24, 2023</td>
                <td class="py-5 px-6 whitespace-nowrap">
                     <div class="flex items-center justify-center gap-3">
                        <img src="https://ui-avatars.com/api/?name=Dr.+Elena+Thorne&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                        <div class="flex flex-col leading-snug">
                            <span class="text-sm font-medium text-slate-500">Dr.</span>
                            <span class="text-sm font-medium text-slate-500">Elena</span>
                            <span class="text-sm font-medium text-slate-500">Thorne</span>
                        </div>
                    </div>
                </td>
                <td class="py-5 px-6 text-center whitespace-nowrap">
                    <span class="font-extrabold text-slate-900 text-[15px]">25.000</span>
                </td>
                <td class="py-5 px-6 text-center whitespace-nowrap">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 text-sm font-bold text-[#E8820C] hover:text-[#c46908] transition-colors cursor-pointer"
                        onclick="openTranscriptModal()"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        View
                    </button>
                </td>
                <td class="py-5 px-6 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                        <button
                            type="button"
                            class="bg-[#10B981] hover:bg-[#059669] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
                            onclick="openPaymentModal('approve')"
                        >Approve</button>
                        <button
                            type="button"
                            class="bg-[#F43F5E] hover:bg-[#E11D48] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer"
                            onclick="openPaymentModal('decline')"
                        >Decline</button>
                    </div>
                </td>
            </tr>
            @endforeach
        @endcomponent
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- PAYROLL TAB                                                 -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div id="tabPayroll" class="hidden">
        <!-- Pending Payouts Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Pending Payouts</h1>
            <p class="text-4xl text-slate-900 tracking-tight font-normal">Rp. 42.891.200,00</p>
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
            ]])
                @foreach(range(1, 7) as $item)
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="py-5 px-6 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=David+Elledge&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                            <span class="text-sm font-bold text-slate-900">David Elledge</span>
                        </div>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900">180.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-[#E8820C]">25.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-medium text-slate-500">14</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <button
                            type="button"
                            class="bg-[#E8820C] hover:bg-[#d0740a] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-[0_4px_14px_0_rgba(232,130,12,0.35)] cursor-pointer"
                            onclick="openReleaseModal()"
                        >
                            Release<br>Payment
                        </button>
                    </td>
                </tr>
                @endforeach

                @foreach(range(1, 8) as $item)
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="py-5 px-6 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=David+Elledge&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                            <span class="text-sm font-bold text-slate-900">David Elledge</span>
                        </div>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900">180.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-[#E8820C]">25.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-medium text-slate-500">14</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <button
                            type="button"
                            class="bg-[#10B981] hover:bg-[#059669] text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm cursor-pointer"
                            onclick="openSelesaiModal()"
                        >
                            Selesai
                        </button>
                    </td>
                </tr>
                @endforeach
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
            ]])
                @foreach(range(1, 7) as $item)
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="py-5 px-6 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=David+Elledge&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                            <span class="text-sm font-bold text-slate-900">David Elledge</span>
                        </div>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900">180.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-[#E8820C]">25.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-medium text-slate-500">14</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <button
                            type="button"
                            class="bg-[#E8820C] hover:bg-[#d0740a] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-[0_4px_14px_0_rgba(232,130,12,0.35)] cursor-pointer"
                            onclick="openReleaseModal()"
                        >
                            Release<br>Payment
                        </button>
                    </td>
                </tr>
                @endforeach
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
            ]])
                @foreach(range(1, 8) as $item)
                <tr class="group hover:bg-slate-50 transition-colors">
                    <td class="py-5 px-6 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <img src="https://ui-avatars.com/api/?name=David+Elledge&background=ececec&color=333333&rounded=true&bold=true" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100">
                            <span class="text-sm font-bold text-slate-900">David Elledge</span>
                        </div>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900">180.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-bold text-[#E8820C]">25.000</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <span class="text-sm font-medium text-slate-500">14</span>
                    </td>
                    <td class="py-5 px-6 text-center whitespace-nowrap">
                        <button
                            type="button"
                            class="bg-[#10B981] hover:bg-[#059669] text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm cursor-pointer"
                            onclick="openSelesaiModal()"
                        >
                            Selesai
                        </button>
                    </td>
                </tr>
                @endforeach
            @endcomponent
        </div>
    </div>
</div>

{{-- Include Modal Components --}}
@include('admin.components.consultations.modal-transcript')
@include('admin.components.consultations.modal-payment')
@include('admin.components.consultations.modal-release')
@include('admin.components.consultations.modal-selesai')

{{-- Modal & Tab Scripts --}}
<script>
    // ─── Generic modal helpers ───────────────────────────────────────
    function showModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function hideModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    // Close modal on backdrop click or close-button click
    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-modal-backdrop]')) {
            e.target.closest('[data-modal]').classList.add('hidden');
        }
        if (e.target.closest('[data-modal-close]')) {
            e.target.closest('[data-modal]').classList.add('hidden');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach(m => m.classList.add('hidden'));
        }
    });

    // ─── Transcript modal ────────────────────────────────────────────
    function openTranscriptModal() {
        showModal('transcriptModal');
    }

    // ─── Payment modal ───────────────────────────────────────────────
    function openPaymentModal(type) {
        const modal = document.getElementById('paymentModal');
        const iconWrapper = modal.querySelector('[data-payment-icon-wrapper]');
        const icon = modal.querySelector('[data-payment-icon]');
        const title = modal.querySelector('[data-payment-title]');
        const description = modal.querySelector('[data-payment-description]');
        const confirmBtn = modal.querySelector('[data-payment-confirm]');

        if (type === 'approve') {
            iconWrapper.style.background = 'linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%)';
            icon.setAttribute('stroke', '#10B981');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            title.textContent = 'Approve Payment?';
            description.textContent = 'You are about to approve the payment for this consultation. This action will notify the architect and release the funds.';
            confirmBtn.textContent = 'Yes, Approve';
            confirmBtn.style.backgroundColor = '#10B981';
            confirmBtn.style.boxShadow = '0 10px 25px -8px rgba(16,185,129,0.5)';
        } else {
            iconWrapper.style.background = 'linear-gradient(135deg, #FFE4E6 0%, #FECDD3 100%)';
            icon.setAttribute('stroke', '#F43F5E');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            title.textContent = 'Decline Payment?';
            description.textContent = 'You are about to decline the payment for this consultation. The requester will be notified and the funds will be returned.';
            confirmBtn.textContent = 'Yes, Decline';
            confirmBtn.style.backgroundColor = '#F43F5E';
            confirmBtn.style.boxShadow = '0 10px 25px -8px rgba(244,63,94,0.5)';
        }

        showModal('paymentModal');
    }

    // ─── Release modal ───────────────────────────────────────────────
    function openReleaseModal() {
        showModal('releaseModal');
    }

    // ─── Selesai modal ───────────────────────────────────────────────
    function openSelesaiModal() {
        showModal('selesaiModal');
    }

    // ─── Tab switching (Report / Payroll) ────────────────────────────
    function switchTab(tab) {
        const reportTab = document.getElementById('tabReport');
        const payrollTab = document.getElementById('tabPayroll');
        const btnReport = document.getElementById('tabBtnReport');
        const btnPayroll = document.getElementById('tabBtnPayroll');

        const activeClasses = 'bg-[#E8820C] text-white shadow-sm';
        const inactiveClasses = 'bg-white text-[#E8820C] hover:bg-orange-50';

        if (tab === 'report') {
            reportTab.classList.remove('hidden');
            payrollTab.classList.add('hidden');
            btnReport.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${activeClasses}`;
            btnPayroll.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${inactiveClasses}`;
        } else {
            reportTab.classList.add('hidden');
            payrollTab.classList.remove('hidden');
            btnReport.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${inactiveClasses}`;
            btnPayroll.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${activeClasses}`;
        }
    }

    // ─── Payroll filter switching (All / Pay / History) ────────────────
    function switchPayrollSub(sub) {
        const allPanel = document.getElementById('payrollSubAll');
        const payPanel = document.getElementById('payrollSubPay');
        const historyPanel = document.getElementById('payrollSubHistory');
        const filter = document.getElementById('payrollSubFilter');

        if (!allPanel || !payPanel || !historyPanel) return;

        if (filter && filter.value !== sub) {
            filter.value = sub;
        }

        if (sub === 'pay') {
            allPanel.classList.add('hidden');
            payPanel.classList.remove('hidden');
            historyPanel.classList.add('hidden');
        } else if (sub === 'history') {
            allPanel.classList.add('hidden');
            payPanel.classList.add('hidden');
            historyPanel.classList.remove('hidden');
        } else {
            allPanel.classList.remove('hidden');
            payPanel.classList.add('hidden');
            historyPanel.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        switchPayrollSub('all');
    });
</script>
@endsection
