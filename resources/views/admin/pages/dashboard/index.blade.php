@extends('admin.layout.dashboard')

@section('title', 'Dashboard - HaloSitek')

@section('content')
<div
    id="dashboard-growth"
    class="dashboard-shell space-y-8"
    data-stats-url="{{ route('admin.dashboard.stats') }}"
    data-user-growth-url="{{ route('admin.dashboard.user-growth') }}"
    data-architect-growth-url="{{ route('admin.dashboard.architect-growth') }}"
>

    <!-- Header Section -->
    <div>
        <h1 class="dashboard-section-title mb-1 text-2xl font-bold tracking-tight">Analytics Overview</h1>
        <p class="dashboard-section-subtitle text-sm">Monitoring growth and engagement across HaloSitek platform.</p>
    </div>

    <!-- Top Stats Cards -->
    <div class="dashboard-stat-grid">
        <!-- Stat Card 1 -->
        <div class="dashboard-card relative overflow-hidden">
            <div class="dashboard-card-body">
                <div class="dashboard-stat-icon">
               <img src="{{ asset('images/dashboard/user-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Users">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Registered Users</p>
                <h3 id="total-users" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="dashboard-card relative overflow-hidden">
            <div class="dashboard-card-body">
                <div class="dashboard-stat-icon">
                 <img src="{{ asset('images/dashboard/architect-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Architects">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Registered Architect</p>
                <h3 id="total-architects" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="dashboard-card relative overflow-hidden">
            <div class="dashboard-card-body">
                <div class="dashboard-stat-icon">
                <img src="{{ asset('images/dashboard/design-icon-orange.png') }}" class="w-6 h-6 object-contain" alt="Designs">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Total Design</p>
                <h3 id="total-designs" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>
    </div>

    <div class="dashboard-growth-grid">
        <section class="dashboard-card">
            <div class="dashboard-card-body">
                <div class="dashboard-growth-header">
                <div>
                    <p class="dashboard-growth-title text-xl font-bold tracking-tight">User Growth</p>
                    <h2 id="user-growth-total" class="dashboard-growth-value mt-1 text-4xl font-bold tracking-tight">0</h2>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        class="growth-menu-toggle dashboard-growth-menu-toggle text-xs font-semibold"
                        data-target="user-growth-menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span id="user-growth-selected-label">Last 7 days</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="user-growth-menu" class="growth-menu dashboard-growth-menu hidden">
                        <button
                            type="button"
                            class="user-growth-period dashboard-growth-menu-item text-xs font-semibold"
                            data-period="7d"
                            data-label="Last 7 days"
                            data-active="true"
                        >
                            Last 7 days
                        </button>
                        <button
                            type="button"
                            class="user-growth-period dashboard-growth-menu-item text-xs font-semibold"
                            data-period="30d"
                            data-label="Last 30 days"
                            data-active="false"
                        >
                            Last 30 days
                        </button>
                    </div>
                </div>
                </div>

                <div class="dashboard-growth-chart-wrap">
                    <div class="dashboard-growth-chart-box">
                    <svg id="user-growth-chart" class="h-full w-full" viewBox="0 0 640 240" preserveAspectRatio="none" role="img" aria-label="New user registrations over time"></svg>
                    <div id="user-growth-empty" class="absolute inset-0 hidden"></div>
                </div>
                    <div id="user-growth-labels" class="dashboard-growth-labels text-xs font-semibold"></div>
                </div>
            </div>
        </section>

        <section class="dashboard-card">
            <div class="dashboard-card-body">
                <div class="dashboard-growth-header">
                <div>
                    <p class="dashboard-growth-title text-xl font-bold tracking-tight">Architect Growth</p>
                    <h2 id="architect-growth-total" class="dashboard-growth-value mt-1 text-4xl font-bold tracking-tight">0</h2>
                </div>

                <div class="relative">
                    <button
                        type="button"
                        class="growth-menu-toggle dashboard-growth-menu-toggle text-xs font-semibold"
                        data-target="architect-growth-menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span id="architect-growth-selected-label">Last 7 days</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="architect-growth-menu" class="growth-menu dashboard-growth-menu hidden">
                        <button
                            type="button"
                            class="architect-growth-period dashboard-growth-menu-item text-xs font-semibold"
                            data-period="7d"
                            data-label="Last 7 days"
                            data-active="true"
                        >
                            Last 7 days
                        </button>
                        <button
                            type="button"
                            class="architect-growth-period dashboard-growth-menu-item text-xs font-semibold"
                            data-period="30d"
                            data-label="Last 30 days"
                            data-active="false"
                        >
                            Last 30 days
                        </button>
                    </div>
                </div>
                </div>

                <div class="dashboard-growth-chart-wrap">
                    <div class="dashboard-growth-chart-box">
                    <svg id="architect-growth-chart" class="h-full w-full" viewBox="0 0 640 240" preserveAspectRatio="none" role="img" aria-label="New approved architects over time"></svg>
                    <div id="architect-growth-empty" class="absolute inset-0 hidden"></div>
                </div>
                    <div id="architect-growth-labels" class="dashboard-growth-labels text-xs font-semibold"></div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection