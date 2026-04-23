@extends('admin.layout.dashboard')

@section('title', 'Design - HaloSitek')

@section('content')
<div class="mx-auto max-w-7xl pb-12">
    <div class="mb-7 flex items-center gap-3">
        <a
            href="{{ route('admin.dashboard.index') }}"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:text-[#D97706]"
            aria-label="Back to dashboard"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6" />
            </svg>
        </a>

        <div class="flex-1">
            <h1 class="text-[28px] font-bold tracking-tight text-slate-900">Design Gallery Overview</h1>
            <p class="mt-1 text-sm text-slate-500">Explore project submissions from the database in a reusable card layout.</p>
        </div>

        <div class="hidden rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-right sm:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#E8820C]">Total projects</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($projects->total()) }}</p>
        </div>
    </div>

    @if ($projects->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">No projects yet</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Design cards will appear here once projects are created</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                This page is connected to the `projects` collection, so any new approved or pending project can be rendered through the same reusable component.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($projects as $project)
                <x-admin.design-card :project="$project" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection
