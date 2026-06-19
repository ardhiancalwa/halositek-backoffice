document.addEventListener('DOMContentLoaded', () => {
    const PER_PAGE = 10;

    // ─── DOM References ─────────────────────────────────────────────
    const consultationsWrapper = document.getElementById('consultations-wrapper');
    const reportDataUrl = consultationsWrapper?.dataset.reportUrl;
    const payrollDataUrl = consultationsWrapper?.dataset.payrollUrl;
    const reportStatsUrl = consultationsWrapper?.dataset.reportStatsUrl;
    const payrollSummaryUrl = consultationsWrapper?.dataset.payrollSummaryUrl;
    const architectConsultationsUrl = consultationsWrapper?.dataset.architectConsultationsUrl;
    const releasePayrollUrl = consultationsWrapper?.dataset.releasePayrollUrl;
    const transcriptUrl = consultationsWrapper?.dataset.transcriptUrl;
    const reportStatusUrl = consultationsWrapper?.dataset.reportStatusUrl;

    // Report elements
    const reportTableBody = document.getElementById('report-table-body');
    const reportPrevBtn = document.getElementById('report-prev-page');
    const reportNextBtn = document.getElementById('report-next-page');
    const reportPaginationNumbers = document.getElementById('report-pagination-numbers');
    const reportCurrentPage = document.getElementById('report-current-page');
    const reportTotalPages = document.getElementById('report-total-pages');
    const reportFilterBtns = document.querySelectorAll('.report-filter-btn');

    // Payroll elements (all / pay / history)
    const payrollTableBodies = {
        all: document.getElementById('payroll-table-body-all'),
        pay: document.getElementById('payroll-table-body-pay'),
        history: document.getElementById('payroll-table-body-history'),
    };
    const payrollPagination = {
        all: {
            prev: document.getElementById('payroll-all-prev-page'),
            next: document.getElementById('payroll-all-next-page'),
            numbers: document.getElementById('payroll-all-pagination-numbers'),
            current: document.getElementById('payroll-all-current-page'),
            total: document.getElementById('payroll-all-total-pages'),
        },
        pay: {
            prev: document.getElementById('payroll-pay-prev-page'),
            next: document.getElementById('payroll-pay-next-page'),
            numbers: document.getElementById('payroll-pay-pagination-numbers'),
            current: document.getElementById('payroll-pay-current-page'),
            total: document.getElementById('payroll-pay-total-pages'),
        },
        history: {
            prev: document.getElementById('payroll-history-prev-page'),
            next: document.getElementById('payroll-history-next-page'),
            numbers: document.getElementById('payroll-history-pagination-numbers'),
            current: document.getElementById('payroll-history-current-page'),
            total: document.getElementById('payroll-history-total-pages'),
        },
    };

    // Tab elements
    const tabBtnReport = document.getElementById('tabBtnReport');
    const tabBtnPayroll = document.getElementById('tabBtnPayroll');
    const tabReport = document.getElementById('tabReport');
    const tabPayroll = document.getElementById('tabPayroll');
    const payrollSubFilter = document.getElementById('payrollSubFilter');

    // ─── State ──────────────────────────────────────────────────────
    let reportState = { page: 1, filter: 'all' };
    let payrollState = {
        all: { page: 1 },
        pay: { page: 1 },
        history: { page: 1 },
        currentArchitectId: null,
        currentReportId: null,
        currentAction: null,
    };

    // ─── Loading Spinner HTML ───────────────────────────────────────
    function getLoadingRow(colspan) {
        return `<tr><td colspan="${colspan}" class="px-6 py-12 text-center text-slate-500">
            <div class="flex items-center justify-center gap-3">
                <svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm font-medium text-slate-400">Loading data...</span>
            </div>
        </td></tr>`;
    }

    function getEmptyRow(colspan, message) {
        return `<tr><td colspan="${colspan}" class="px-6 py-12 text-center text-slate-400">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <span class="text-sm font-medium">${message}</span>
            </div>
        </td></tr>`;
    }

    // ─── Pagination Generator ───────────────────────────────────────
    // Generates: < 1 2 3 ... 125 > style pagination
    function generatePageNumbers(currentPage, totalPages) {
        const pages = [];

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            // Always show page 1
            pages.push(1);

            if (currentPage <= 3) {
                pages.push(2, 3);
                pages.push('...');
                pages.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                pages.push('...');
                pages.push(totalPages - 2, totalPages - 1, totalPages);
            } else {
                pages.push('...');
                pages.push(currentPage - 1, currentPage, currentPage + 1);
                pages.push('...');
                pages.push(totalPages);
            }
        }

        return pages;
    }

    function renderPaginationUI(meta, elements, onPageClick) {
        const totalPages = meta.last_page || 1;
        const currentPageNum = meta.current_page || 1;

        if (elements.current) elements.current.textContent = currentPageNum;
        if (elements.total) elements.total.textContent = totalPages;

        if (elements.prev) elements.prev.disabled = currentPageNum === 1;
        if (elements.next) elements.next.disabled = currentPageNum === totalPages;

        if (!elements.numbers) return;

        elements.numbers.innerHTML = '';
        const pageNumbers = generatePageNumbers(currentPageNum, totalPages);

        pageNumbers.forEach((page) => {
            if (page === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'dashboard-pagination-number text-sm font-medium pointer-events-none opacity-60';
                elements.numbers.appendChild(ellipsis);
            } else {
                const btn = document.createElement('button');
                btn.textContent = page;
                btn.type = 'button';
                btn.className = `dashboard-pagination-number text-sm font-medium ${page === currentPageNum ? 'is-active' : ''}`;
                btn.addEventListener('click', () => onPageClick(page));
                elements.numbers.appendChild(btn);
            }
        });

        // Wire prev/next buttons
        if (elements.prev) {
            elements.prev.onclick = () => {
                if (currentPageNum > 1) onPageClick(currentPageNum - 1);
            };
        }
        if (elements.next) {
            elements.next.onclick = () => {
                if (currentPageNum < totalPages) onPageClick(currentPageNum + 1);
            };
        }
    }

    // ─── Fetch Helpers ──────────────────────────────────────────────
    async function fetchData(url, params) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = `${url}?${queryString}`;

        try {
            const response = await fetch(fullUrl, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) return null;
            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            return null;
        }
    }

    // ─── Number formatting ──────────────────────────────────────────
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function formatCurrency(num) {
        return 'Rp. ' + new Intl.NumberFormat('id-ID').format(num) + ',00';
    }

    function truncateText(text, maxLen) {
        if (!text) return '-';
        return text.length > maxLen ? text.substring(0, maxLen) + '...' : text;
    }

    // ═══════════════════════════════════════════════════════════════
    // REPORT TAB
    // ═══════════════════════════════════════════════════════════════
    function renderReportRow(item) {
        const roleLabel = (item.requester_role || 'user').toUpperCase();
        const nameParts = (item.requester_name || 'Unknown').split(' ');
        const displayName = nameParts.length > 1
            ? nameParts.slice(0, 2).join('<br>')
            : nameParts[0];

        const opposingParts = (item.opposing_party_name || 'Unknown').split(' ');
        const opposingDisplay = opposingParts.map(p => `<span class="text-sm font-medium text-slate-500">${p}</span>`).join('');

        return `
        <tr class="group hover:bg-slate-50 transition-colors">
            <td class="py-5 px-6 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <img src="${item.requester_avatar}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100" alt="${item.requester_name}">
                    <div class="flex flex-col">
                        <span class="text-sm font-extrabold text-slate-900">${displayName}</span>
                        <span class="text-[9px] font-black bg-slate-100/80 text-slate-500 px-2 py-0.5 rounded uppercase w-max mt-1 tracking-wider">${roleLabel}</span>
                    </div>
                </div>
            </td>
            <td class="py-5 px-6 text-sm text-slate-500 font-medium">${truncateText(item.reason, 30)}</td>
            <td class="py-5 px-6 text-sm text-slate-500 font-medium text-center whitespace-nowrap">${item.consultation_date}</td>
            <td class="py-5 px-6 whitespace-nowrap">
                <div class="flex items-center justify-center gap-3">
                    <img src="${item.opposing_party_avatar}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100" alt="${item.opposing_party_name}">
                    <div class="flex flex-col leading-snug">
                        ${opposingDisplay}
                    </div>
                </div>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <span class="font-extrabold text-slate-900 text-[15px]">${formatNumber(item.session_fee)}</span>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <button type="button" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#E8820C] hover:text-[#c46908] transition-colors cursor-pointer" onclick="openTranscriptModal('${item.consultation_id}')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    View
                </button>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-2">
                    <button type="button" class="bg-[#10B981] hover:bg-[#059669] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer" onclick="openPaymentModal('approve', '${item.id}')">Approve</button>
                    <button type="button" class="bg-[#F43F5E] hover:bg-[#E11D48] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-sm cursor-pointer" onclick="openPaymentModal('decline', '${item.id}')">Decline</button>
                </div>
            </td>
        </tr>`;
    }

    async function loadReportPage(page) {
        if (!reportTableBody || !reportDataUrl) return;

        reportTableBody.innerHTML = getLoadingRow(7);

        const data = await fetchData(reportDataUrl, {
            page: page,
            per_page: PER_PAGE,
            role: reportState.filter,
        });

        if (!data || !data.data) {
            reportTableBody.innerHTML = getEmptyRow(7, 'No reports found');
            return;
        }

        const items = data.data || [];
        if (items.length === 0) {
            reportTableBody.innerHTML = getEmptyRow(7, 'No reports found');
        } else {
            reportTableBody.innerHTML = items.map(renderReportRow).join('');
        }

        reportState.page = page;

        renderPaginationUI(data.meta || {}, {
            prev: reportPrevBtn,
            next: reportNextBtn,
            numbers: reportPaginationNumbers,
            current: reportCurrentPage,
            total: reportTotalPages,
        }, loadReportPage);
    }

    // Report filter buttons
    const activeFilterClasses = ['bg-[#E8820C]', 'text-white', 'shadow-[0_4px_14px_0_rgba(232,130,12,0.39)]'];
    const inactiveFilterClasses = ['bg-white', 'text-slate-600', 'border', 'border-slate-200', 'shadow-sm', 'hover:bg-slate-50'];

    reportFilterBtns.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // Reset all buttons to inactive
            reportFilterBtns.forEach((b) => {
                activeFilterClasses.forEach((c) => b.classList.remove(c));
                inactiveFilterClasses.forEach((c) => b.classList.add(c));
                b.removeAttribute('data-active');
            });

            // Set clicked button to active
            const target = e.currentTarget;
            inactiveFilterClasses.forEach((c) => target.classList.remove(c));
            activeFilterClasses.forEach((c) => target.classList.add(c));
            target.setAttribute('data-active', 'true');

            reportState.filter = target.dataset.filter;
            reportState.page = 1;
            loadReportPage(1);
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // PAYROLL TAB
    // ═══════════════════════════════════════════════════════════════
    function renderPayrollRow(item, isPay) {
        const actionButton = isPay
            ? `<button type="button" class="bg-[#E8820C] hover:bg-[#d0740a] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all shadow-[0_4px_14px_0_rgba(232,130,12,0.35)] cursor-pointer" onclick="openReleaseModal('${item.architect_id}')">Release<br>Payment</button>`
            : `<button type="button" class="bg-[#10B981] hover:bg-[#059669] text-white px-5 py-2.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all shadow-sm cursor-pointer" onclick="openSelesaiModal('${item.architect_id}')">Selesai</button>`;

        return `
        <tr class="group hover:bg-slate-50 transition-colors">
            <td class="py-5 px-6 whitespace-nowrap">
                <div class="flex items-center gap-3">
                    <img src="${item.architect_avatar}" class="w-10 h-10 rounded-full object-cover shadow-sm border border-slate-100" alt="${item.architect_name}">
                    <span class="text-sm font-bold text-slate-900">${item.architect_name}</span>
                </div>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <span class="text-sm font-bold text-slate-900">${formatNumber(item.total_earnings)}</span>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <span class="text-sm font-bold text-[#E8820C]">${formatNumber(item.per_session)}</span>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                <span class="text-sm font-medium text-slate-500">${item.total_consultations}</span>
            </td>
            <td class="py-5 px-6 text-center whitespace-nowrap">
                ${actionButton}
            </td>
        </tr>`;
    }

    async function loadPayrollPage(subFilter, page) {
        const tableBody = payrollTableBodies[subFilter];
        const pag = payrollPagination[subFilter];

        if (!tableBody || !payrollDataUrl) return;

        tableBody.innerHTML = getLoadingRow(5);

        const filterMap = { all: 'all', pay: 'pay', history: 'history' };
        const data = await fetchData(payrollDataUrl, {
            page: page,
            per_page: PER_PAGE,
            filter: filterMap[subFilter] || 'all',
        });

        if (!data || !data.data) {
            tableBody.innerHTML = getEmptyRow(5, 'No payroll data found');
            return;
        }

        const items = data.data || [];
        if (items.length === 0) {
            tableBody.innerHTML = getEmptyRow(5, 'No payroll data found');
        } else {
            const isPay = subFilter === 'pay' || subFilter === 'all';
            if (subFilter === 'all') {
                // In "all" mode, show release for pending, selesai for released
                tableBody.innerHTML = items.map((item) => {
                    const showRelease = item.payout_status !== 'released';
                    return renderPayrollRow(item, showRelease);
                }).join('');
            } else {
                tableBody.innerHTML = items.map((item) => renderPayrollRow(item, subFilter === 'pay')).join('');
            }
        }

        payrollState[subFilter].page = page;

        if (pag) {
            renderPaginationUI(data.meta || {}, pag, (p) => loadPayrollPage(subFilter, p));
        }
    }

    // ─── Tab switching (Report / Payroll) ────────────────────────────
    function switchTab(tab) {
        const activeClasses = 'bg-[#E8820C] text-white shadow-sm';
        const inactiveClasses = 'bg-white text-[#E8820C] hover:bg-orange-50';

        if (tab === 'report') {
            tabReport.classList.remove('hidden');
            tabPayroll.classList.add('hidden');
            tabBtnReport.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${activeClasses}`;
            tabBtnPayroll.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${inactiveClasses}`;
        } else {
            tabReport.classList.add('hidden');
            tabPayroll.classList.remove('hidden');
            tabBtnReport.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${inactiveClasses}`;
            tabBtnPayroll.className = `px-10 py-2.5 rounded-lg text-sm font-bold transition-all cursor-pointer ${activeClasses}`;

            // Load payroll summary + data on switch
            loadPayrollSummary();
            const currentSub = payrollSubFilter?.value || 'all';
            loadPayrollPage(currentSub, payrollState[currentSub].page);
        }
    }

    // Expose tab switching globally
    window.switchTab = switchTab;

    // ─── Payroll filter switching (All / Pay / History) ────────────────
    function switchPayrollSub(sub) {
        const allPanel = document.getElementById('payrollSubAll');
        const payPanel = document.getElementById('payrollSubPay');
        const historyPanel = document.getElementById('payrollSubHistory');

        if (!allPanel || !payPanel || !historyPanel) return;

        if (payrollSubFilter && payrollSubFilter.value !== sub) {
            payrollSubFilter.value = sub;
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

        // Load data for the selected sub-filter
        loadPayrollPage(sub, 1);
    }

    // Expose payroll sub switching globally
    window.switchPayrollSub = switchPayrollSub;

    // ─── Generic modal helpers (moved from inline script) ───────────
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
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach((m) => m.classList.add('hidden'));
        }
    });

    // ─── Modal openers (exposed globally) ───────────────────────────
    // ─── Modal openers (exposed globally) ───────────────────────────
    window.openTranscriptModal = async function (consultationId) {
        const modal = document.getElementById('transcriptModal');
        const messagesContainer = modal.querySelector('[data-transcript-messages]');
        const dateEl = modal.querySelector('[data-transcript-date]');
        const reqNameEl = modal.querySelector('[data-transcript-requester-name]');
        const archNameEl = modal.querySelector('[data-transcript-architect-name]');

        if (!messagesContainer) return;

        messagesContainer.innerHTML = '<div class="py-10 text-center text-slate-400">Loading transcript...</div>';
        showModal('transcriptModal');

        const url = transcriptUrl.replace(':id', consultationId);
        const res = await fetchData(url, {});

        if (res && res.data) {
            const data = res.data;
            if (dateEl) dateEl.textContent = data.date;
            if (reqNameEl) reqNameEl.textContent = data.user_name;
            if (archNameEl) archNameEl.textContent = data.architect_name;

            // Render transcript. If it's a string, show as a system message or single bubble
            const content = data.transcript || 'No transcript available.';

            messagesContainer.innerHTML = `
                <div class="flex flex-col items-start">
                    <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">System / Full History</p>
                    <div class="max-w-full rounded-2xl bg-white border border-slate-100 px-4 py-3 shadow-sm">
                        <p class="text-[13px] leading-relaxed text-slate-700 whitespace-pre-line">${content}</p>
                    </div>
                </div>
            `;
        } else {
            messagesContainer.innerHTML = '<div class="py-10 text-center text-red-400">Failed to load transcript.</div>';
        }
    };

    window.openPaymentModal = async function (type, reportId) {
        payrollState.currentReportId = reportId;
        payrollState.currentAction = type;

        const modal = document.getElementById('paymentModal');
        const iconWrapper = modal.querySelector('[data-payment-icon-wrapper]');
        const icon = modal.querySelector('[data-payment-icon]');
        const title = modal.querySelector('[data-payment-title]');
        const description = modal.querySelector('[data-payment-description]');
        const confirmBtn = modal.querySelector('[data-payment-confirm]');

        // We can find the row data from the table to populate small details
        const row = document.querySelector(`button[onclick*="'${reportId}'"]`)?.closest('tr');
        if (row) {
            const reqName = row.querySelector('.text-sm.font-extrabold')?.textContent;
            const reqAvatar = row.querySelector('img')?.src;
            const date = row.cells[2]?.textContent;
            const amount = row.cells[4]?.textContent;

            if (modal.querySelector('[data-payment-requester]')) modal.querySelector('[data-payment-requester]').textContent = reqName;
            if (modal.querySelector('[data-payment-avatar]')) modal.querySelector('[data-payment-avatar]').src = reqAvatar;
            if (modal.querySelector('[data-payment-date]')) modal.querySelector('[data-payment-date]').textContent = date;
            if (modal.querySelector('[data-payment-amount]')) modal.querySelector('[data-payment-amount]').textContent = `Rp ${amount}`;
        }

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
    };

    const paymentConfirmBtn = document.querySelector('[data-payment-confirm]');
    if (paymentConfirmBtn) {
        paymentConfirmBtn.addEventListener('click', async () => {
            if (!payrollState.currentReportId || !payrollState.currentAction) return;

            const reportId = payrollState.currentReportId;
            const status = payrollState.currentAction === 'approve' ? 'approved' : 'declined';
            const url = reportStatusUrl.replace(':id', reportId);

            paymentConfirmBtn.disabled = true;
            paymentConfirmBtn.textContent = 'Processing...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status })
                });

                const result = await response.json();

                if (result.success) {
                    hideModal('paymentModal');
                    loadReportPage(reportState.page);
                    loadReportStats();
                } else {
                    alert(result.message || 'Failed to update report status.');
                }
            } catch (error) {
                console.error('Status update error:', error);
                alert('An error occurred while updating report status.');
            } finally {
                paymentConfirmBtn.disabled = false;
                paymentConfirmBtn.textContent = `Yes, ${status.charAt(0).toUpperCase() + status.slice(1)}`;
            }
        });
    }

    window.openReleaseModal = async function (architectId) {
        payrollState.currentArchitectId = architectId;
        const modal = document.getElementById('releaseModal');
        const tableBody = document.getElementById('release-table-body');
        const perSessionEl = document.getElementById('release-per-session');
        const totalUsersEl = document.getElementById('release-total-users');
        const totalAmountEl = document.getElementById('release-total-amount');

        if (!tableBody) return;

        tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-slate-400">Loading...</td></tr>`;
        showModal('releaseModal');

        const url = architectConsultationsUrl.replace(':id', architectId) + '?status=pending';
        const res = await fetchData(url, {});

        if (res && res.data) {
            const data = res.data;
            const items = data.release_payment_items || [];
            const summary = data.payment_summary || {};

            tableBody.innerHTML = items.map(item => `
                <tr>
                    <td class="py-2.5 pr-4"><span class="text-sm font-bold text-slate-800">${item.user_name}</span></td>
                    <td class="py-2.5 pr-4"><span class="text-sm text-slate-400">${item.date}</span></td>
                    <td class="py-2.5 pr-4 text-right"><span class="text-sm font-bold text-slate-800">${formatNumber(item.fee)}</span></td>
                    <td class="py-2.5 text-right"><span class="text-[9px] font-black uppercase tracking-wider text-[#10B981]">${item.status}</span></td>
                </tr>
            `).join('');

            perSessionEl.textContent = formatNumber(summary.consultation_per_session || 0);
            totalUsersEl.textContent = summary.total_user_consultation || 0;
            totalAmountEl.textContent = `Rp. ${formatNumber(data.total_amount || 0)}`;
        }
    };

    window.openSelesaiModal = async function (architectId) {
        const modal = document.getElementById('selesaiModal');
        const tableBody = document.getElementById('selesai-table-body');
        const perSessionEl = document.getElementById('selesai-per-session');
        const totalUsersEl = document.getElementById('selesai-total-users');
        const totalAmountEl = document.getElementById('selesai-total-amount');
        const dateEl = modal.querySelector('[data-selesai-date]');

        if (!tableBody) return;

        tableBody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-slate-400">Loading...</td></tr>`;
        if (dateEl) dateEl.textContent = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        showModal('selesaiModal');

        const url = architectConsultationsUrl.replace(':id', architectId) + '?status=released';
        const res = await fetchData(url, {});

        if (res && res.data) {
            const data = res.data;
            const items = data.release_payment_items || [];
            const summary = data.payment_summary || {};

            tableBody.innerHTML = items.map(item => `
                <tr>
                    <td class="py-2.5 pr-4"><span class="text-sm font-bold text-slate-800">${item.user_name}</span></td>
                    <td class="py-2.5 pr-4"><span class="text-sm text-slate-400">${item.date}</span></td>
                    <td class="py-2.5 pr-4 text-right"><span class="text-sm font-bold text-slate-800">${formatNumber(item.fee)}</span></td>
                    <td class="py-2.5 text-right"><span class="text-[9px] font-black uppercase tracking-wider text-[#10B981]">${item.status}</span></td>
                </tr>
            `).join('');

            perSessionEl.textContent = formatNumber(summary.consultation_per_session || 0);
            totalUsersEl.textContent = summary.total_user_consultation || 0;
            totalAmountEl.textContent = `Rp. ${formatNumber(data.total_amount || 0)}`;
        }
    };

    const releaseConfirmBtn = document.querySelector('[data-release-confirm]');
    if (releaseConfirmBtn) {
        releaseConfirmBtn.addEventListener('click', async () => {
            if (!payrollState.currentArchitectId) return;

            const architectId = payrollState.currentArchitectId;
            const url = releasePayrollUrl.replace(':id', architectId);

            releaseConfirmBtn.disabled = true;
            releaseConfirmBtn.textContent = 'Processing...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    hideModal('releaseModal');
                    // Refresh data
                    payrollSummaryLoaded = false;
                    loadPayrollSummary();
                    const currentSub = payrollSubFilter?.value || 'all';
                    loadPayrollPage(currentSub, payrollState[currentSub].page);
                } else {
                    alert(result.message || 'Failed to release payroll.');
                }
            } catch (error) {
                console.error('Release error:', error);
                alert('An error occurred while releasing payroll.');
            } finally {
                releaseConfirmBtn.disabled = false;
                releaseConfirmBtn.textContent = 'Release';
            }
        });
    }

    // ─── Initialize ─────────────────────────────────────────────────
    loadReportStats();
    loadReportPage(1);
});

// ═══════════════════════════════════════════════════════════════
// STATS LOADING (outside DOMContentLoaded for proper scoping)
// ═══════════════════════════════════════════════════════════════
async function loadReportStats() {
    const wrapper = document.getElementById('consultations-wrapper');
    const url = wrapper?.dataset.reportStatsUrl;
    if (!url) return;

    try {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const json = await res.json();
        const data = json.data;
        if (!data) return;

        const statMap = {
            total_report: { value: data.total_report ?? 0 },
            new_report: { value: data.new_report ?? 0 },
            user_report: { value: data.user_report ?? 0 },
            architect_report: { value: data.architect_report ?? 0 },
        };

        const total = data.total_report || 1; // avoid division by zero

        Object.entries(statMap).forEach(([key, info]) => {
            const valueEl = document.getElementById(`stat-${key}`);
            const barEl = document.getElementById(`stat-bar-${key}`);

            if (valueEl) {
                valueEl.innerHTML = `<h3 class="text-4xl font-black text-slate-900 tracking-tight">${new Intl.NumberFormat('id-ID').format(info.value)}</h3>`;
            }

            if (barEl) {
                const percent = Math.min(100, Math.round((info.value / total) * 100));
                // Use key-specific logic: total_report always shows its own ratio
                const barPercent = key === 'total_report'
                    ? Math.min(100, Math.max(5, Math.round((info.value / Math.max(info.value, 500)) * 100)))
                    : Math.min(100, Math.max(5, percent));
                setTimeout(() => { barEl.style.width = barPercent + '%'; }, 100);
            }
        });
    } catch (err) {
        console.error('Failed to load report stats:', err);
    }
}

let payrollSummaryLoaded = false;

async function loadPayrollSummary() {
    if (payrollSummaryLoaded) return;
    payrollSummaryLoaded = true;

    const wrapper = document.getElementById('consultations-wrapper');
    const url = wrapper?.dataset.payrollSummaryUrl;
    const amountEl = document.getElementById('payroll-pending-amount');
    if (!url || !amountEl) return;

    try {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) return;
        const json = await res.json();
        const amount = json.data?.pending_payouts ?? 0;

        amountEl.innerHTML = `<p class="text-4xl text-slate-900 tracking-tight font-normal">Rp. ${new Intl.NumberFormat('id-ID').format(amount)},00</p>`;
    } catch (err) {
        console.error('Failed to load payroll summary:', err);
        amountEl.innerHTML = '<p class="text-4xl text-slate-900 tracking-tight font-normal">Rp. 0</p>';
    }
}
