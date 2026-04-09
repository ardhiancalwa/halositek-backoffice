const forms = document.querySelectorAll('[data-auth-form]');

const storageKeys = {
    accessToken: 'halositek.auth.access_token',
    refreshToken: 'halositek.auth.refresh_token',
    tokenType: 'halositek.auth.token_type',
    expiresIn: 'halositek.auth.expires_in',
    user: 'halositek.auth.user',
};

const hideAlert = (element) => {
    if (!element) {
        return;
    }

    element.classList.add('hidden');
    element.textContent = '';
    element.classList.remove('border-red-200', 'bg-red-50', 'text-red-600', 'border-green-200', 'bg-green-50', 'text-green-700');
};

const showAlert = (element, type, message) => {
    if (!element) {
        return;
    }

    element.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-600', 'border-green-200', 'bg-green-50', 'text-green-700');

    if (type === 'error') {
        element.classList.add('border-red-200', 'bg-red-50', 'text-red-600');
    } else {
        element.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
    }

    element.textContent = message;
};

const setSubmitting = (button, isSubmitting, idleLabel) => {
    if (!button) {
        return;
    }

    button.disabled = isSubmitting;
    button.textContent = isSubmitting ? 'Please wait...' : idleLabel;
    button.classList.toggle('opacity-70', isSubmitting);
    button.classList.toggle('cursor-not-allowed', isSubmitting);
};

const normalizeErrorMessage = (data) => {
    if (data?.message) {
        return data.message;
    }

    if (data?.errors && typeof data.errors === 'object') {
        const firstErrorGroup = Object.values(data.errors)[0];

        if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
            return firstErrorGroup[0];
        }
    }

    return 'Something went wrong. Please try again.';
};

const storeAuthPayload = (data) => {
    localStorage.setItem(storageKeys.accessToken, data.access_token ?? '');
    localStorage.setItem(storageKeys.refreshToken, data.refresh_token ?? '');
    localStorage.setItem(storageKeys.tokenType, data.token_type ?? 'Bearer');
    localStorage.setItem(storageKeys.expiresIn, String(data.expires_in ?? ''));

    const user = {
        id: data.id ?? null,
        name: data.name ?? null,
        email: data.email ?? null,
        role: data.role ?? null,
    };

    localStorage.setItem(storageKeys.user, JSON.stringify(user));
};

forms.forEach((form) => {
    const alert = form.parentElement?.querySelector('[data-auth-alert]');
    const submitButton = form.querySelector('[data-auth-submit]');
    const idleLabel = submitButton?.textContent?.trim() || 'Submit';

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const termsInput = form.querySelector('input[name="terms"]');

        if (termsInput instanceof HTMLInputElement && !termsInput.checked) {
            showAlert(alert, 'error', 'You must accept the terms to continue.');
            return;
        }

        hideAlert(alert);
        setSubmitting(submitButton, true, idleLabel);

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        if (form.dataset.authMode === 'register' && !payload.role) {
            payload.role = 'user';
        }

        delete payload.terms;

        try {
            const response = await window.axios.post(form.dataset.authEndpoint, payload);
            const data = response.data?.data ?? {};

            storeAuthPayload(data);

            const successMessage = form.dataset.authMode === 'register'
                ? 'Registration successful. Redirecting...'
                : 'Login successful. Redirecting...';

            showAlert(alert, 'success', successMessage);

            window.setTimeout(() => {
                window.location.href = form.dataset.authRedirect || '/';
            }, 800);
        } catch (error) {
            const message = normalizeErrorMessage(error.response?.data);
            showAlert(alert, 'error', message);
        } finally {
            setSubmitting(submitButton, false, idleLabel);
        }
    });
});
