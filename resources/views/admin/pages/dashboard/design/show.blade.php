@extends('admin.layout.dashboard')

@section('title', 'Project Detail - HaloSitek')

@section('content')
<div class="mx-auto max-w-7xl pb-12">
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Please fix the highlighted fields and try again.</p>
        </div>
    @endif

    <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.dashboard.designs.index') }}"
                class="inline-flex items-center text-slate-800 transition hover:text-[#D97706]"
                aria-label="Back to design management"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-5 py-2 text-sm font-semibold text-red-500 transition hover:bg-red-50"
                data-delete-modal-open
            >
                Delete Design
            </button>

            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" id="design-delete-form">
                @csrf
                @method('DELETE')
            </form>

            <button
                type="submit"
                form="design-update-form"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#D97706] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#B45309]"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>

    <form
        id="design-update-form"
        method="POST"
        action="{{ route('admin.projects.update', $project) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        @include('admin.components.designs.design-project-detail', ['project' => $project])
    </form>

    <div
        class="dashboard-confirm-overlay hidden"
        data-delete-modal
        aria-hidden="true"
    >
        <div
            class="dashboard-confirm-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-design-modal-title"
        >
            <div class="dashboard-confirm-header">
                <div class="dashboard-confirm-icon">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 id="delete-design-modal-title" class="dashboard-confirm-title text-lg font-bold tracking-tight">
                        Are you sure?
                    </h2>
                    <p class="dashboard-confirm-copy text-sm leading-6">
                        This will permanently delete <span class="font-semibold text-slate-900">{{ $project->name }}</span> and remove its saved design media.
                    </p>
                </div>
            </div>

            <div class="px-6 pt-5">
                <div class="dashboard-modal-highlight">
                    <p class="text-sm font-medium text-slate-700">
                        This action cannot be undone.
                    </p>
                </div>
            </div>

            <div class="dashboard-confirm-actions">
                <button type="button" class="dashboard-confirm-secondary-button text-sm font-semibold" data-delete-modal-close>
                    Cancel
                </button>
                <button type="submit" form="design-delete-form" class="dashboard-confirm-danger-button text-sm font-semibold">
                    Yes, Delete Design
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.querySelector('[data-delete-modal]');
        const openButton = document.querySelector('[data-delete-modal-open]');
        const closeButtons = document.querySelectorAll('[data-delete-modal-close]');
        const counterInputs = document.querySelectorAll('[data-char-input]');

        const formatCount = (count) => String(count).padStart(2, '0');

        counterInputs.forEach((input) => {
            const counter = input.parentElement?.querySelector('[data-char-count]');
            const max = input.dataset.charMax;

            if (!counter || !max) {
                return;
            }

            const updateCounter = () => {
                counter.textContent = `${formatCount(input.value.length)}/${max}`;
            };

            updateCounter();
            input.addEventListener('input', updateCounter);
        });

        if (!modal || !openButton) {
            return;
        }

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        };

        openButton.addEventListener('click', openModal);

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });
    });
</script>
@endpush
