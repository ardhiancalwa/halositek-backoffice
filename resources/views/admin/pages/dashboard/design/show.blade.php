@extends('admin.layout.dashboard')

@section('title', 'Project Detail - HaloSitek')

@section('content')
<div class="mx-auto max-w-7xl pb-12">
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
            >
                Delete Design
            </button>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#D97706] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#B45309]"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>

    @include('admin.components.design-project-detail', ['project' => $project])
</div>
@endsection
php artisan migrate:fresh