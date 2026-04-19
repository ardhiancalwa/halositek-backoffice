@extends('admin.layout.dashboard')

@section('title', 'Design - HaloSitek')

@section('content')
@php
    $designs = collect([
        [
            'title' => 'Skyline Modern Villa',
            'meta' => '340 sqm • 4 Bedrooms',
            'badge' => 'MODERN',
            'surface' => 'bg-[linear-gradient(180deg,rgba(233,243,249,0.9)_0%,rgba(199,223,236,0.9)_100%)]',
            'art' => 'villa',
        ],
        [
            'title' => 'Nexus Tech Hub',
            'meta' => '120 sqm • Office Space',
            'badge' => 'MINIMALIST',
            'surface' => 'bg-[linear-gradient(180deg,#7f9d7f_0%,#6c8b6f_100%)]',
            'art' => 'minimal',
        ],
        [
            'title' => 'Pine Ridge Eco-Cabin',
            'meta' => '850 sqm • 1 Bedroom',
            'badge' => 'TRADITIONAL',
            'surface' => 'bg-[linear-gradient(180deg,#7f945f_0%,#58723d_100%)]',
            'art' => 'cabin',
        ],
        [
            'title' => 'The Zenith Retreat',
            'meta' => '210 sqm • 3 Bedrooms',
            'badge' => 'MODERN',
            'surface' => 'bg-[linear-gradient(90deg,#87643d_0%,#4c331e_38%,#caa36d_70%,#e6d3b0_100%)]',
            'art' => 'retreat',
        ],
    ])->multiply(4);
@endphp

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

        <div>
            <h1 class="text-[28px] font-bold tracking-tight text-slate-900">Design Gallery Overview</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($designs as $design)
            <article class="group overflow-hidden rounded-2xl border border-[#EEF1F5] bg-white shadow-[0_12px_30px_-26px_rgba(15,23,42,0.55)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-26px_rgba(15,23,42,0.65)]">
                <div class="relative h-36 overflow-hidden {{ $design['surface'] }}">
                    @if ($design['art'] === 'villa')
                        <div class="absolute inset-x-5 bottom-0 top-7 rounded-t-xl bg-[#f6f7f8] shadow-[0_0_0_1px_rgba(148,163,184,0.12)]">
                            <div class="absolute inset-x-6 bottom-0 h-9 bg-[#7b4f34]"></div>
                            <div class="absolute left-7 top-5 h-16 w-14 bg-[#ded7cf]"></div>
                            <div class="absolute left-[5.25rem] top-3 h-20 w-14 bg-[#efebe6]"></div>
                            <div class="absolute right-7 top-7 h-14 w-16 bg-[#f8f6f2]"></div>
                            <div class="absolute left-10 top-8 h-8 w-6 bg-[#90a9ba]"></div>
                            <div class="absolute left-[5.9rem] top-6 h-10 w-6 bg-[#90a9ba]"></div>
                            <div class="absolute right-11 top-10 h-7 w-7 bg-[#90a9ba]"></div>
                            <div class="absolute inset-x-0 bottom-0 h-3 bg-[#72956c]"></div>
                        </div>
                    @elseif ($design['art'] === 'minimal')
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center text-[34px] font-semibold uppercase leading-none tracking-[-0.06em] text-white/14">
                                <div>mini</div>
                                <div>mal</div>
                                <div>style</div>
                            </div>
                        </div>
                    @elseif ($design['art'] === 'cabin')
                        <div class="absolute inset-x-6 bottom-0 top-4">
                            <div class="absolute inset-x-0 top-0 h-12 rounded-t-md bg-[#8f5b2b] [clip-path:polygon(50%_0%,100%_100%,0%_100%)]"></div>
                            <div class="absolute inset-x-5 bottom-0 top-9 rounded-t-sm bg-[#a86a30] shadow-[0_0_0_1px_rgba(66,39,15,0.2)]">
                                <div class="absolute inset-y-0 left-3 w-1 bg-[#6b3f1a]"></div>
                                <div class="absolute inset-y-0 left-10 w-1 bg-[#6b3f1a]"></div>
                                <div class="absolute inset-y-0 right-10 w-1 bg-[#6b3f1a]"></div>
                                <div class="absolute inset-y-0 right-3 w-1 bg-[#6b3f1a]"></div>
                                <div class="absolute inset-y-4 left-[42%] w-8 rounded-sm border border-[#6b3f1a]"></div>
                            </div>
                        </div>
                    @else
                        <div class="absolute inset-y-0 left-0 w-10 bg-[repeating-linear-gradient(90deg,rgba(70,41,16,0.9)_0_3px,rgba(124,82,40,0.65)_3px_7px)]"></div>
                        <div class="absolute inset-y-0 right-0 w-10 bg-[repeating-linear-gradient(90deg,rgba(70,41,16,0.9)_0_3px,rgba(124,82,40,0.65)_3px_7px)]"></div>
                        <div class="absolute inset-y-6 left-10 right-10 rounded-sm bg-[linear-gradient(180deg,#e9f6d9_0%,#9fcf79_100%)]"></div>
                    @endif

                    <span class="absolute right-2 top-2 rounded-md bg-black/55 px-2 py-1 text-[10px] font-bold tracking-wide text-white">
                        {{ $design['badge'] }}
                    </span>
                </div>

                <div class="p-4">
                    <h2 class="mb-1 text-[15px] font-bold leading-5 text-slate-900">{{ $design['title'] }}</h2>
                    <p class="mb-4 text-xs font-medium text-slate-400">{{ $design['meta'] }}</p>

                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-100 bg-white py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Manage
                    </button>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
