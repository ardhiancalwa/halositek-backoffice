document.addEventListener('DOMContentLoaded', async () => {
    let currentPage = 1;
    let selectedStatus = 'all';
    const perPage = 15;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const tableWrapper = document.getElementById('users-table-wrapper');
    const usersUrl = tableWrapper?.dataset.usersUrl;
    const userUpdateUrlTemplate = tableWrapper?.dataset.userUpdateUrlTemplate;
    const statusFilterBtns = document.querySelectorAll('.status-filter-btn');
    const tableBody = document.querySelector('#users-table-wrapper tbody');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const paginationNumbers = document.getElementById('pagination-numbers');
    const currentPageSpan = document.getElementById('current-page');
    const totalPagesSpan = document.getElementById('total-pages');
    const modalRoot = document.getElementById('user-status-modal');
    const modalCloseButtons = modalRoot?.querySelectorAll('[data-modal-close]');
    const modalOverlay = modalRoot?.querySelector('[data-modal-overlay]');
    const modalAvatar = document.getElementById('user-status-modal-avatar');
    const editAdminName = document.getElementById('edit_admin_name');
    const editAdminPassword = document.getElementById('edit_admin_password');
    const editAdminEmail = document.getElementById('edit_admin_email');
    const btnTriggerUpdate = document.getElementById('btn-trigger-update');
    const confirmUpdateModal = document.getElementById('confirm-update-modal');
    const btnConfirmUpdate = document.getElementById('btn-confirm-update');
    const statusForm = document.getElementById('user-status-form');
    const statusUserIdInput = document.getElementById('user-status-id');
    const statusSelect = document.getElementById('user-status-select');
    const statusFeedback = document.getElementById('user-status-feedback');
    const usersById = new Map();
    let selectedUser = null;

    if (!tableWrapper || !usersUrl || !userUpdateUrlTemplate || !tableBody || !prevPageBtn || !nextPageBtn || !paginationNumbers || !currentPageSpan || !totalPagesSpan || !modalRoot || !statusForm || !statusUserIdInput || !statusSelect || !statusFeedback || !modalAvatar) {
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
            return `
                <span class="dashboard-status-badge dashboard-status-badge-active">
                    <span class="dashboard-status-badge-icon">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                    <span>Active</span>
                </span>
            `;
        }

        if (status === 'suspend') {
            return `
                <span class="dashboard-status-badge dashboard-status-badge-suspended">
                    <span class="dashboard-status-badge-icon">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 6l12 12M18 6L6 18"></path>
                        </svg>
                    </span>
                    <span>Suspended</span>
                </span>
            `;
        }

        return `<span class="text-xs font-semibold">${status?.toUpperCase()}</span>`;
    }

    function getAvatarUrl(user) {
        if (user.photo_profile) {
            return user.photo_profile.startsWith('http') ? user.photo_profile : user.photo_profile_url;
        }
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=F1F5F9&color=475569&size=80`;
    }

    function setModalOpen(isOpen) {
        modalRoot.classList.toggle('hidden', !isOpen);
        modalRoot.classList.toggle('flex', isOpen);
        document.body.classList.toggle('overflow-hidden', isOpen);
    }

    function openStatusModal(user) {
        selectedUser = user;
        statusFeedback.textContent = '';
        statusFeedback.className = 'text-sm text-slate-500';
        statusUserIdInput.value = user.id;
        statusSelect.value = user.account_status;
        modalAvatar.src = getAvatarUrl(user);
        modalAvatar.alt = user.name;
        
        editAdminName.value = user.name;
        editAdminEmail.value = user.email;
        editAdminPassword.value = ''; // Don't show existing pass

        setModalOpen(true);
    }

    function closeStatusModal() {
        selectedUser = null;
        setModalOpen(false);
    }

    async function updateUser(userId, data) {
        try {
            const response = await fetch(userUpdateUrlTemplate.replace('__ID__', userId), {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok) {
                return {
                    success: false,
                    message: payload?.message || 'Failed to update user.',
                };
            }

            return {
                success: true,
                message: payload?.message || 'User updated successfully.',
                user: payload?.data?.user ?? null,
            };
        } catch (error) {
            console.error('Error updating user:', error);

            return {
                success: false,
                message: 'An unexpected error occurred while updating the status.',
            };
        }
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
        usersById.clear();

        users.forEach((user) => {
            usersById.set(String(user.id), user);
        });

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
                                src="${getAvatarUrl(user)}"
                                alt="${user.name}"
                                class="h-full w-full object-cover"
                            >
                        </div>
                        <span class="text-sm font-medium text-slate-900">${user.name}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${user.email}</td>
                <td class="px-6 py-4 text-sm text-slate-600">********</td>
                <td class="px-6 py-4">
                    ${getStatusBadge(user.account_status)}
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" class="view-user-btn dashboard-table-action-button p-2 text-slate-400 hover:text-slate-600 transition" title="View user" data-user-id="${user.id}">
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

        let pageStart = Math.max(1, currentPageNum - 2);
        let pageEnd = Math.min(totalPages, currentPageNum + 2);

        if ((pageEnd - pageStart) < 4) {
            if (pageStart === 1) {
                pageEnd = Math.min(totalPages, pageStart + 4);
            } else if (pageEnd === totalPages) {
                pageStart = Math.max(1, pageEnd - 4);
            }
        }

        for (let i = pageStart; i <= pageEnd; i += 1) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.type = 'button';
            btn.className = `dashboard-pagination-number text-sm font-medium ${i === currentPageNum ? 'is-active' : ''}`;
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
                button.classList.remove('is-active');
            });

            const target = event.currentTarget;
            target.classList.add('is-active');

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

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('.view-user-btn');

        if (!button) {
            return;
        }

        const userId = button.getAttribute('data-user-id');

        if (!userId) {
            return;
        }

        const user = usersById.get(userId);

        if (!user) {
            return;
        }

        openStatusModal(user);
    });

    modalCloseButtons?.forEach((button) => {
        button.addEventListener('click', closeStatusModal);
    });

    modalOverlay?.addEventListener('click', closeStatusModal);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modalRoot.classList.contains('hidden')) {
            closeStatusModal();
        }
    });

    btnTriggerUpdate.addEventListener('click', (event) => {
        event.preventDefault();

        if (!selectedUser) {
            return;
        }
        
        if (statusForm.checkValidity()) {
            setModalOpen(false);
            confirmUpdateModal.classList.remove('hidden');
            confirmUpdateModal.classList.add('flex');
        } else {
            statusForm.reportValidity();
        }
    });

    btnConfirmUpdate.addEventListener('click', async () => {
        if (!selectedUser) return;

        btnConfirmUpdate.disabled = true;
        btnConfirmUpdate.textContent = 'Updating...';

        const dataToUpdate = {
            name: editAdminName.value,
            email: editAdminEmail.value,
            account_status: statusSelect.value,
        };

        if (editAdminPassword.value.trim() !== '') {
            dataToUpdate.password = editAdminPassword.value;
        }

        const result = await updateUser(selectedUser.id, dataToUpdate);

        btnConfirmUpdate.disabled = false;
        btnConfirmUpdate.textContent = 'Save Changes';

        confirmUpdateModal.classList.add('hidden');
        confirmUpdateModal.classList.remove('flex');

        if (!result.success) {
            alert(result.message);
            setModalOpen(true);
            return;
        }

        closeStatusModal();
        await loadPage(currentPage);
    });

    // Add Admin Functionality
    const addAdminBtn = document.getElementById('add-admin-btn');
    const addAdminModal = document.getElementById('add-admin-modal');
    const addAdminForm = document.getElementById('add-admin-form');
    const btnProvisionAdmin = document.getElementById('btn-provision-admin');
    const confirmAdminModal = document.getElementById('confirm-admin-modal');
    const btnConfirmAdd = document.getElementById('btn-confirm-add');

    // Close functionality for the Add Admin Modal
    const closeAddAdminModal = () => {
        addAdminModal.classList.add('hidden');
        addAdminModal.classList.remove('flex');
        addAdminForm.reset();
    };

    addAdminModal.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', closeAddAdminModal);
    });

    addAdminBtn.addEventListener('click', () => {
        addAdminModal.classList.remove('hidden');
        addAdminModal.classList.add('flex');
    });

    btnProvisionAdmin.addEventListener('click', () => {
        if (addAdminForm.checkValidity()) {
            addAdminModal.classList.add('hidden');
            addAdminModal.classList.remove('flex');
            confirmAdminModal.classList.remove('hidden');
            confirmAdminModal.classList.add('flex');
        } else {
            addAdminForm.reportValidity();
        }
    });

    btnConfirmAdd.addEventListener('click', async () => {
        const btn = btnConfirmAdd;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Provising...';

        const formData = new FormData(addAdminForm);
        const url = `${usersUrl.split('?')[0].replace('/data', '')}`; 

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(Object.fromEntries(formData.entries()))
            });

            if (response.ok) {
                confirmAdminModal.classList.add('hidden');
                confirmAdminModal.classList.remove('flex');
                closeAddAdminModal();
                await loadPage(currentPage);
                // Also update stats if they exist, reload the page to refresh stats easily
                window.location.reload(); 
            } else {
                const err = await response.json();
                alert(err.message || 'Failed to add admin');
            }
        } catch (e) {
            alert('Failed to connect to server');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
            confirmAdminModal.classList.add('hidden');
            confirmAdminModal.classList.remove('flex');
        }
    });

    await loadPage(1);
});
