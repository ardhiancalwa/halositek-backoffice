@extends('admin.layout.dashboard')

@section('title', 'Dashboard - HaloSitek')

@section('content')
<div
    id="dashboard-growth"
    class="max-w-7xl mx-auto space-y-8 pb-12"
    data-stats-url="{{ route('dashboard.stats') }}"
    data-user-growth-url="{{ route('dashboard.user-growth') }}"
    data-architect-growth-url="{{ route('dashboard.architect-growth') }}"
>

    <!-- Header Section -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 mb-1 tracking-tight">Analytics Overview</h1>
        <p class="text-sm text-slate-500">Monitoring growth and engagement across HaloSitek platform.</p>
    </div>

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stat Card 1 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-[#E8820C] mb-4">
               <img src="{{ asset('images/dashboard/user-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Users">
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Registered Users</p>
            <h3 id="total-users" class="text-3xl font-bold text-slate-900 tracking-tight">...</h3>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-[#E8820C] mb-4">
                 <img src="{{ asset('images/dashboard/architect-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Architects">
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Registered Architect</p>
            <h3 id="total-architects" class="text-3xl font-bold text-slate-900 tracking-tight">...</h3>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-[#E8820C] mb-4">
                <img src="{{ asset('images/dashboard/design-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Designs">
            </div>
            <p class="text-sm text-slate-500 font-medium mb-1">Total Design</p>
            <h3 id="total-designs" class="text-3xl font-bold text-slate-900 tracking-tight">...</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <section class="min-w-0 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xl font-bold tracking-tight text-slate-900">User Growth</p>
                    <h2 id="user-growth-total" class="mt-1 text-4xl font-bold tracking-tight text-slate-900">0</h2>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        class="growth-menu-toggle inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        data-target="user-growth-menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span id="user-growth-selected-label">Last 7 days</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="user-growth-menu" class="growth-menu absolute right-0 top-full z-20 mt-2 hidden min-w-[144px] rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                        <button
                            type="button"
                            class="user-growth-period flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 data-[active=true]:bg-orange-50 data-[active=true]:text-[#E8820C]"
                            data-period="7d"
                            data-label="Last 7 days"
                            data-active="true"
                        >
                            Last 7 days
                        </button>
                        <button
                            type="button"
                            class="user-growth-period flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 data-[active=true]:bg-orange-50 data-[active=true]:text-[#E8820C]"
                            data-period="30d"
                            data-label="Last 30 days"
                            data-active="false"
                        >
                            Last 30 days
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div class="relative h-48">
                    <svg id="user-growth-chart" class="h-full w-full" viewBox="0 0 640 240" preserveAspectRatio="none" role="img" aria-label="New user registrations over time"></svg>
                    <div id="user-growth-empty" class="absolute inset-0 hidden"></div>
                </div>
                <div id="user-growth-labels" class="mt-5 grid gap-2 text-xs font-semibold text-slate-400"></div>
            </div>
        </section>

        <section class="min-w-0 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xl font-bold tracking-tight text-slate-900">Architect Growth</p>
                    <h2 id="architect-growth-total" class="mt-1 text-4xl font-bold tracking-tight text-slate-900">0</h2>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        class="growth-menu-toggle inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                        data-target="architect-growth-menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span id="architect-growth-selected-label">Last 7 days</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="architect-growth-menu" class="growth-menu absolute right-0 top-full z-20 mt-2 hidden min-w-[144px] rounded-xl border border-slate-200 bg-white p-1 shadow-lg">
                        <button
                            type="button"
                            class="architect-growth-period flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 data-[active=true]:bg-orange-50 data-[active=true]:text-[#E8820C]"
                            data-period="7d"
                            data-label="Last 7 days"
                            data-active="true"
                        >
                            Last 7 days
                        </button>
                        <button
                            type="button"
                            class="architect-growth-period flex w-full items-center rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 data-[active=true]:bg-orange-50 data-[active=true]:text-[#E8820C]"
                            data-period="30d"
                            data-label="Last 30 days"
                            data-active="false"
                        >
                            Last 30 days
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div class="relative h-48">
                    <svg id="architect-growth-chart" class="h-full w-full" viewBox="0 0 640 240" preserveAspectRatio="none" role="img" aria-label="New approved architects over time"></svg>
                    <div id="architect-growth-empty" class="absolute inset-0 hidden"></div>
                </div>
                <div id="architect-growth-labels" class="mt-5 grid gap-2 text-xs font-semibold text-slate-400"></div>
            </div>
        </section>
    </div>

    <!-- Design Gallery Section -->
    <div class="pt-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Design Gallery Overview</h2>
            <a href="{{ route('designs.index') }}" class="text-sm font-semibold text-[#E8820C] hover:text-[#d4750a] transition-colors">View All</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $designs = [
                    ['title' => 'Skyline Modern Villa', 'meta' => '340 m² • 4 Bedrooms', 'badge' => 'MODERN'],
                    ['title' => 'Nexus Tech Hub', 'meta' => '120 m² • Office Space', 'badge' => 'COMMERCIAL'],
                    ['title' => 'Pine Ridge Eco-Cabin', 'meta' => '850 m² • 1 Bedroom', 'badge' => 'TRADITIONAL'],
                    ['title' => 'The Zenith Retreat', 'meta' => '210 m² • 3 Bedrooms', 'badge' => 'MODERN'],
                ];
            @endphp
            @foreach($designs as $design)
            <div class="bg-white border rounded-2xl border-slate-100 shadow-sm p-4 flex flex-col hover:shadow-md transition-shadow">
                <div class="h-32 bg-slate-200 rounded-xl mb-4 relative overflow-hidden">
                    <!-- Placeholder for Image -->
                    <div class="absolute top-2 right-2 px-2 py-1 bg-black/60 text-white text-[10px] font-bold rounded tracking-wider">{{ $design['badge'] }}</div>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1 line-clamp-1">{{ $design['title'] }}</h3>
                <p class="text-xs text-slate-500 mb-4">{{ $design['meta'] }}</p>
                <div class="mt-auto">
                    <button class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors border border-slate-100">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Manage
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
