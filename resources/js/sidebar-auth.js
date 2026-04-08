const storageKeys = {
    accessToken: 'halositek.auth.access_token',
    refreshToken: 'halositek.auth.refresh_token',
    tokenType: 'halositek.auth.token_type',
    expiresIn: 'halositek.auth.expires_in',
    user: 'halositek.auth.user',
};

const parseStoredUser = () => {
    const rawUser = window.localStorage.getItem(storageKeys.user);

    if (!rawUser) {
        return null;
    }

    try {
        return JSON.parse(rawUser);
    } catch {
        return null;
    }
};

const clearStoredAuth = () => {
    Object.values(storageKeys).forEach((key) => {
        window.localStorage.removeItem(key);
    });
};

const getInitials = (name) => {
    if (!name) {
        return '?';
    }

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('') || '?';
};

const titleize = (value) => {
    if (!value) {
        return 'User';
    }

    return String(value)
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
};

document.querySelectorAll('[data-sidebar-auth]').forEach((sidebar) => {
    const user = parseStoredUser();
    const avatar = sidebar.querySelector('[data-sidebar-avatar]');
    const avatarFallback = sidebar.querySelector('[data-sidebar-avatar-fallback]');
    const nameElement = sidebar.querySelector('[data-sidebar-name]');
    const roleElement = sidebar.querySelector('[data-sidebar-role]');
    const logoutButton = sidebar.querySelector('[data-sidebar-logout]');

    if (user) {
        const displayName = user.name || 'User';
        const displayRole = titleize(user.role);
        const photoUrl = user.profile_photo_url || user.avatar || user.photo_url || null;

        if (nameElement) {
            nameElement.textContent = displayName;
        }

        if (roleElement) {
            roleElement.textContent = displayRole;
        }

        if (avatarFallback) {
            avatarFallback.textContent = getInitials(displayName);
        }

        if (avatar instanceof HTMLImageElement) {
            avatar.alt = displayName;

            if (photoUrl) {
                avatar.src = photoUrl;
            }
        }
    }

    if (avatar instanceof HTMLImageElement && avatarFallback) {
        const toggleFallback = () => {
            avatarFallback.classList.toggle('hidden', avatar.complete && avatar.naturalWidth > 0);
        };

        avatar.addEventListener('load', toggleFallback);
        avatar.addEventListener('error', () => {
            avatarFallback.classList.remove('hidden');
        });
        toggleFallback();
    }

    if (!(logoutButton instanceof HTMLButtonElement)) {
        return;
    }

    logoutButton.addEventListener('click', async () => {
        const accessToken = window.localStorage.getItem(storageKeys.accessToken);
        const refreshToken = window.localStorage.getItem(storageKeys.refreshToken);
        const logoutUrl = sidebar.dataset.logoutUrl;
        const loginUrl = sidebar.dataset.loginUrl || '/login';

        logoutButton.disabled = true;

        try {
            if (accessToken && logoutUrl) {
                await window.axios.post(
                    logoutUrl,
                    refreshToken ? { refresh_token: refreshToken } : {},
                    {
                        headers: {
                            Authorization: `Bearer ${accessToken}`,
                            Accept: 'application/json',
                        },
                    }
                );
            }
        } catch (error) {
            console.error('Logout request failed:', error);
        } finally {
            clearStoredAuth();
            window.location.href = loginUrl;
        }
    });
});
