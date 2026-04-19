document.addEventListener('DOMContentLoaded', async () => {
    let currentPage = 1;
    let selectedStatus = 'all';
    const perPage = 15;

    const tableWrapper = document.getElementById('users-table-wrapper');
    const usersUrl = tableWrapper?.dataset.usersUrl;
    const statusFilterBtns = document.querySelectorAll('.status-filter-btn');
    const tableBody = document.querySelector('#users-table-wrapper tbody');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const paginationNumbers = document.getElementById('pagination-numbers');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');

    if (!tableWrapper || !usersUrl || !tableBody || !prevPageBtn || !nextPageBtn || !paginationNumbers || !currentPageSpan || !totalPagesSpan) {
        return;
    }

    function formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    function getStatusBadge(status) {
        if (status === 'active') {
            return '<span class="inline-flex items-center gap-2 text-xs font-semibold"><span class="h-2 w-2 rounded-full bg-green-500"></span> ACTIVE</span>';
        }

        if (status === 'suspend') {
            return '<span class="inline-flex items-center gap-2 text-xs font-semibold text-red-600"><span class="h-2 w-2 rounded-full bg-red-500"></span> SUSPENDED</span>';
        }

        return `<span class="text-xs font-semibold">${status?.toUpperCase()}</span>`;
    }

    async function fetchUsers(page = 1, status = null) {
        let url = `${usersUrl}?page=${page}&per_page=${perPage}`;

        if (status && status !== 'all') {
            url += `&status=${status}`;
        }

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching users:', error);
            return null;
        }
    }

    function renderUsers(data) {
        const users = data.data || [];

        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No users found</td></tr>';
            return;
        }

        tableBody.innerHTML = users.map((user) => `
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(data) {
        const meta = data.meta || {};
        const totalPages = meta.last_page || 1;
        const currentPageNum = meta.current_page || 1;

        currentPageSpan.textContent = currentPageNum;
        totalPagesSpan.textContent = totalPages;

        prevPageBtn.disabled = currentPageNum === 1;
        nextPageBtn.disabled = currentPageNum === totalPages;

        paginationNumbers.innerHTML = '';

        for (let i = 1; i <= Math.min(totalPages, 5); i += 1) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-2 rounded-lg text-sm font-medium transition ${
                i === currentPageNum
                    ? 'bg-[#E8820C] text-white'
                    : 'border border-slate-200 text-slate-600 hover:bg-slate-50'
            }`;
            btn.addEventListener('click', () => {
                loadPage(i);
            });
            paginationNumbers.appendChild(btn);
        }
    }

    async function loadPage(page) {
        const data = await fetchUsers(page, selectedStatus);

        if (data) {
            renderUsers(data);
            renderPagination(data);
            currentPage = page;
        }
    }

    statusFilterBtns.forEach((btn) => {
        btn.addEventListener('click', async (event) => {
            statusFilterBtns.forEach((button) => {
                button.classList.remove('bg-[#E8820C]', 'text-white');
                button.classList.add('bg-slate-100', 'text-slate-700');
            });

            const target = event.currentTarget;
            target.classList.remove('bg-slate-100', 'text-slate-700');
            target.classList.add('bg-[#E8820C]', 'text-white');

            selectedStatus = target.dataset.statusFilter;
            currentPage = 1;
            await loadPage(1);
        });
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            loadPage(currentPage - 1);
        }
    });

    nextPageBtn.addEventListener('click', async () => {
        const data = await fetchUsers(currentPage + 1, selectedStatus);

        if (data && currentPage < (data.meta?.last_page || 1)) {
            loadPage(currentPage + 1);
        }
    });

    await loadPage(1);
});
