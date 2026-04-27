@extends('admin.layout.dashboard')

@section('title', 'Design - HaloSitek')

@section('content')
@php
    $currentPage = $projects->currentPage();
    $lastPage = $projects->lastPage();
    $pageStart = max(1, $currentPage - 2);
    $pageEnd = min($lastPage, $currentPage + 2);
    $pageLinks = [];

    if (($pageEnd - $pageStart) < 4) {
        if ($pageStart === 1) {
            $pageEnd = min($lastPage, $pageStart + 4);
        } elseif ($pageEnd === $lastPage) {
            $pageStart = max(1, $pageEnd - 4);
        }
    }

    for ($page = $pageStart; $page <= $pageEnd; $page++) {
        $pageLinks[] = [
            'label' => $page,
            'url' => $projects->url($page),
            'isActive' => $page === $currentPage,
        ];
    }
@endphp

<div class="mx-auto max-w-7xl pb-12">
    <div class="mb-7 flex items-center gap-3">
        <div class="flex-1">
            <h1 class="text-[28px] font-bold tracking-tight text-slate-900">Design Gallery Overview</h1>
        </div>

        <div class="hidden rounded-2xl border border-orange-100 bg-orange-50 px-4 py-3 text-right sm:block">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#E8820C]">Total projects</p>
            <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($projects->total()) }}</p>
        </div>
    </div>

    <div class="mb-7 flex flex-wrap items-center gap-2">
        <a
            href="{{ route('admin.dashboard.designs.index') }}"
            class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold transition {{ $selectedStyle === null ? 'border-[#D97706] bg-[#D97706] text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}"
        >
            All Design
        </a>

        @foreach ($styleFilters as $styleFilter)
            <a
                href="{{ route('admin.dashboard.designs.index', ['style' => $styleFilter['value']]) }}"
                class="inline-flex items-center rounded-full border px-4 py-2 text-xs font-semibold transition {{ $selectedStyle === $styleFilter['value'] ? 'border-[#D97706] bg-[#D97706] text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}"
            >
                {{ $styleFilter['label'] }}
            </a>
        @endforeach
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
                @include('admin.components.designs.design-card', ['project' => $project])
            @endforeach
        </div>

        @component('admin.components.pagination-footer', [
            'currentPage' => $currentPage,
            'totalPages' => $lastPage,
            'pageLinks' => $pageLinks,
            'previousUrl' => $projects->previousPageUrl(),
            'nextUrl' => $projects->nextPageUrl(),
            'previousDisabled' => $projects->onFirstPage(),
            'nextDisabled' => ! $projects->hasMorePages(),
            'wrapperClass' => 'mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm',
            'footerClass' => 'border-t-0',
        ])
        @endcomponent
    @endif
</div>
@endsection
