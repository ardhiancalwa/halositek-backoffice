document.addEventListener('DOMContentLoaded', () => {
    const PER_PAGE = 10;

    // ─── DOM References ─────────────────────────────────────────────
    const wrapper = document.getElementById('ai-bots-wrapper');
    const logsDataUrl = wrapper?.dataset.logsUrl;

    const tableBody = document.getElementById('ai-bots-table-body');
    const prevBtn = document.getElementById('ai-bots-prev-page');
    const nextBtn = document.getElementById('ai-bots-next-page');
    const paginationNumbers = document.getElementById('ai-bots-pagination-numbers');
    const currentPageEl = document.getElementById('ai-bots-current-page');
    const totalPagesEl = document.getElementById('ai-bots-total-pages');
    const filterBtns = document.querySelectorAll('.ai-bot-filter-btn');

    // ─── State ──────────────────────────────────────────────────────
    let state = { page: 1, filter: 'all' };
    let logsCache = []; // cache current page's logs for modal detail

    // ─── Loading / Empty Rows ───────────────────────────────────────
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

    // ─── Pagination ─────────────────────────────────────────────────
    function generatePageNumbers(currentPage, totalPages) {
        const pages = [];

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
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

    function renderPaginationUI(meta) {
        const totalPages = meta.last_page || 1;
        const currentPageNum = meta.current_page || 1;

        if (currentPageEl) currentPageEl.textContent = currentPageNum;
        if (totalPagesEl) totalPagesEl.textContent = totalPages;
        if (prevBtn) prevBtn.disabled = currentPageNum === 1;
        if (nextBtn) nextBtn.disabled = currentPageNum === totalPages;

        if (!paginationNumbers) return;

        paginationNumbers.innerHTML = '';
        const pageNums = generatePageNumbers(currentPageNum, totalPages);

        pageNums.forEach((page) => {
            if (page === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'dashboard-pagination-number text-sm font-medium pointer-events-none opacity-60';
                paginationNumbers.appendChild(ellipsis);
            } else {
                const btn = document.createElement('button');
                btn.textContent = page;
                btn.type = 'button';
                btn.className = `dashboard-pagination-number text-sm font-medium ${page === currentPageNum ? 'is-active' : ''}`;
                btn.addEventListener('click', () => loadPage(page));
                paginationNumbers.appendChild(btn);
            }
        });

        if (prevBtn) {
            prevBtn.onclick = () => { if (currentPageNum > 1) loadPage(currentPageNum - 1); };
        }
        if (nextBtn) {
            nextBtn.onclick = () => { if (currentPageNum < totalPages) loadPage(currentPageNum + 1); };
        }
    }

    // ─── Fetch Helper ───────────────────────────────────────────────
    async function fetchData(url, params) {
        const qs = new URLSearchParams(params).toString();
        try {
            const res = await fetch(`${url}?${qs}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return null;
            return await res.json();
        } catch (err) {
            console.error('Fetch error:', err);
            return null;
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────
    function formatGenerateTime(ms) {
        if (!ms || ms <= 0) return '-';
        if (ms < 60000) return Math.ceil(ms / 1000) + ' sec';
        return Math.ceil(ms / 60000) + ' min';
    }

    function truncateText(text, maxLen) {
        if (!text) return '-';
        return text.length > maxLen ? text.substring(0, maxLen) + '...' : text;
    }

    // ─── Render Table Row ───────────────────────────────────────────
    function renderRow(item) {
        const statusBadge = item.status === 'success'
            ? '<span class="rounded bg-emerald-50 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-600">Success</span>'
            : '<span class="rounded bg-rose-50 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-rose-600">Failed</span>';

        return `
        <tr class="group transition-colors hover:bg-slate-50" data-ai-bot-row data-status="${item.status}">
            <td class="whitespace-nowrap px-6 py-5">
                <div class="flex items-center gap-3">
                    <img src="${item.user_avatar}" class="h-10 w-10 rounded-full border border-slate-100 object-cover shadow-sm" alt="${item.user_name}">
                    <span class="text-sm font-bold text-slate-900">${item.user_name}</span>
                </div>
            </td>
            <td class="whitespace-nowrap px-6 py-5 text-center">
                <span class="rounded-md bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">${item.date}</span>
            </td>
            <td class="px-6 py-5 text-sm font-medium text-slate-500">
                <span class="line-clamp-1">"${truncateText(item.prompt_preview, 60)}"</span>
            </td>
            <td class="whitespace-nowrap px-6 py-5 text-center">
                ${statusBadge}
            </td>
            <td class="whitespace-nowrap px-6 py-5 text-center text-sm font-medium text-slate-500">${formatGenerateTime(item.generate_time_ms)}</td>
            <td class="whitespace-nowrap px-6 py-5 text-center">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
                    onclick="openAiBotActionModal('${item.id}')"
                    aria-label="View Action Details"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"></path>
                    </svg>
                </button>
            </td>
        </tr>`;
    }

    // ─── Load Page ──────────────────────────────────────────────────
    async function loadPage(page) {
        if (!tableBody || !logsDataUrl) return;

        tableBody.innerHTML = getLoadingRow(6);

        const params = { page, per_page: PER_PAGE };
        if (state.filter !== 'all') params.status = state.filter;

        const data = await fetchData(logsDataUrl, params);

        if (!data || !data.data) {
            tableBody.innerHTML = getEmptyRow(6, 'No activity logs found');
            logsCache = [];
            return;
        }

        const items = data.data || [];
        logsCache = items;

        if (items.length === 0) {
            tableBody.innerHTML = getEmptyRow(6, 'No activity logs found');
        } else {
            tableBody.innerHTML = items.map(renderRow).join('');
        }

        state.page = page;
        renderPaginationUI(data.meta || {});
    }

    // ─── Filter Buttons ─────────────────────────────────────────────
    const activeClasses = {
        all: ['bg-[#E8820C]', 'text-white', 'shadow-[0_4px_14px_0_rgba(232,130,12,0.39)]'],
        success: ['bg-emerald-500', 'text-white', 'shadow-sm'],
        failed: ['bg-rose-500', 'text-white', 'shadow-sm'],
    };
    const inactiveClasses = ['bg-white', 'text-slate-600', 'border', 'border-slate-200', 'shadow-sm', 'hover:bg-slate-50'];

    function switchAiBotFilter(filter) {
        // Update visual state
        filterBtns.forEach((btn) => {
            const btnFilter = btn.dataset.filter;
            const active = activeClasses[btnFilter] || activeClasses.all;

            // Remove all active classes
            Object.values(activeClasses).forEach((cls) => cls.forEach((c) => btn.classList.remove(c)));
            inactiveClasses.forEach((c) => btn.classList.remove(c));

            if (btnFilter === filter) {
                active.forEach((c) => btn.classList.add(c));
            } else {
                inactiveClasses.forEach((c) => btn.classList.add(c));
            }
        });

        state.filter = filter;
        state.page = 1;
        loadPage(1);
    }

    // Expose globally for onclick
    window.switchAiBotFilter = switchAiBotFilter;

    // ─── Modal Logic ────────────────────────────────────────────────
    function showModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function hideModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('click', (e) => {
        if (e.target.matches('[data-modal-backdrop]')) {
            e.target.closest('[data-modal]').classList.add('hidden');
        }
        if (e.target.closest('[data-modal-close]')) {
            e.target.closest('[data-modal]').classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach((m) => m.classList.add('hidden'));
        }
    });

    window.openAiBotActionModal = function (logId) {
        const modal = document.getElementById('aiBotActionModal');
        if (!modal) return;

        const successPanel = modal.querySelector('[data-ai-modal-success]');
        const failurePanel = modal.querySelector('[data-ai-modal-failure]');
        const statusIcon = modal.querySelector('[data-ai-modal-status-icon]');
        const title = modal.querySelector('[data-ai-modal-title]');
        const requestText = modal.querySelector('[data-ai-modal-request]');
        const outputImage = modal.querySelector('[data-ai-modal-output-image]');
        const errorTitle = modal.querySelector('[data-ai-modal-error-title]');
        const errorDetail = modal.querySelector('[data-ai-modal-error-detail]');

        const log = logsCache.find((item) => item.id === logId);
        if (!log) return;

        requestText.textContent = `"${log.request_payload || log.prompt_preview}"`;

        if (log.status === 'success') {
            title.textContent = 'Generation Success Actions';
            statusIcon.className = 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600';
            statusIcon.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>';

            successPanel.classList.remove('hidden');
            failurePanel.classList.add('hidden');

            if (outputImage) {
                outputImage.src = log.generated_image_url || '';
                outputImage.alt = `Generated output by ${log.user_name}`;
            }
        } else {
            title.textContent = 'Generation Failure Actions';
            statusIcon.className = 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600';
            statusIcon.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>';

            successPanel.classList.add('hidden');
            failurePanel.classList.remove('hidden');

            if (errorTitle) errorTitle.textContent = log.error_log ? '[CRITICAL_ERROR] System Error' : '[ERROR] Unknown issue';
            if (errorDetail) errorDetail.textContent = log.error_log || 'No diagnostic details were returned by the system.';
        }

        showModal('aiBotActionModal');
    };

    // ─── Initialize ─────────────────────────────────────────────────
    loadPage(1);
});
