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
                <img src="{{ asset('images/dashboard/icon-user-orange.svg') }}" class="w-6 h-6 object-contain" alt="Users">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Registered Users</p>
                <h3 id="total-users" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="dashboard-card relative overflow-hidden">
            <div class="dashboard-card-body">
                <div class="dashboard-stat-icon">
                 <img src="{{ asset('images/dashboard/icon-architect-orange.svg') }}" class="w-6 h-6 object-contain" alt="Architects">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Registered Architect</p>
                <h3 id="total-architects" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="dashboard-card relative overflow-hidden">
            <div class="dashboard-card-body">
                <div class="dashboard-stat-icon">
                <img src="{{ asset('images/dashboard/icon-design-orange.svg') }}" class="w-6 h-6 object-contain" alt="Designs">
                </div>
                <p class="dashboard-stat-label mb-1 text-sm font-medium">Total Design</p>
                <h3 id="total-designs" class="dashboard-stat-value text-3xl font-bold tracking-tight">...</h3>
            </div>
        </div>
    </div>

    <!-- Design Gallery Overview -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Design Gallery Overview</h2>
            <a href="{{ route('admin.dashboard.designs.index') }}" class="text-sm font-semibold text-[#E8820C] hover:text-[#c46d0a] transition-colors">View All</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($recentApprovedDesigns as $design)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col transition-all hover:shadow-md">
                    <!-- Image Region -->
                    <div class="relative h-40 bg-slate-100">
                        @if(!empty($design->images) && isset($design->images[0]))
                            <img src="{{ $design->images[0] }}" class="w-full h-full object-cover" alt="{{ $design->name }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400">No Image</div>
                        @endif
                        
                        <!-- Badge -->
                        <div class="absolute top-3 right-3 bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold tracking-widest uppercase px-2 py-1 rounded">
                            {{ $design->style?->value ?? 'MODERN' }}
                        </div>
                    </div>
                    
                    <!-- Content Region -->
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="font-bold text-slate-900 text-sm mb-1 line-clamp-1" title="{{ $design->name }}">{{ $design->name }}</h3>
                        <p class="text-xs text-slate-500 mb-4 line-clamp-1">
                            {{ $design->area ?? 'N/A' }} • {{ str_contains(strtolower($design->highlight_features), 'bedroom') ? 'Has Bedrooms' : 'Custom Space' }}
                        </p>
                        
                        <div class="mt-auto">
                            <a href="{{ route('admin.dashboard.designs.index', ['id' => $design->id]) }}" class="flex items-center justify-center w-full py-2 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#E8820C] hover:border-[#E8820C] transition-colors">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Manage
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-1 sm:col-span-2 lg:col-span-4 py-8 text-center text-slate-500 bg-slate-50 rounded-2xl border border-slate-100 border-dashed">
                    No approved designs available yet.
                </div>
            @endforelse
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

    <!-- Applications Tables Region -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Design Applications -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Design Applications</h2>
                <a href="{{ route('admin.dashboard.architects.index', ['type' => 'design']) }}" class="text-xs font-semibold text-[#E8820C] hover:text-[#c46d0a] transition-colors">View all</a>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-y border-slate-100 bg-slate-50/50">
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-2/5">Architect</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-1/4">Style</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-center">Status</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingDesignApplications as $design)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#FFF5EA] text-[#E8820C] flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($design->architect?->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 truncate">{{ $design->architect?->name ?? 'Unknown Architect' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs text-slate-600 font-medium">{{ strtoupper($design->style?->value ?? 'N/A') }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 uppercase tracking-wide">
                                        Pending
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.dashboard.architects.index', ['type' => 'design', 'id' => $design->architect_id]) }}" class="inline-flex items-center justify-center p-1.5 text-slate-400 hover:text-[#E8820C] hover:bg-[#FFF5EA] rounded-md transition-colors" title="View Architecture">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-xs text-slate-500">No pending design applications.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Award Applications -->
        <div class="bg-white border border-slate-100 shadow-sm rounded-2xl p-6 flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Award Applications</h2>
                <a href="{{ route('admin.dashboard.architects.index', ['type' => 'award']) }}" class="text-xs font-semibold text-[#E8820C] hover:text-[#c46d0a] transition-colors">View all</a>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-y border-slate-100 bg-slate-50/50">
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-2/5">Architect</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 w-1/4">Type</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-center">Status</th>
                            <th class="py-3 px-4 text-[10px] font-bold uppercase tracking-widest text-slate-500 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingAwardApplications as $award)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#FFF5EA] text-[#E8820C] flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ strtoupper(substr($award->architect?->name ?? 'U', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 truncate">{{ $award->architect?->name ?? 'Unknown Architect' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs text-slate-600 font-medium">{{ $award->name ?? 'Excellence' }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 uppercase tracking-wide">
                                        Pending
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.dashboard.architects.index', ['type' => 'award', 'id' => $award->architect_id]) }}" class="inline-flex items-center justify-center p-1.5 text-slate-400 hover:text-[#E8820C] hover:bg-[#FFF5EA] rounded-md transition-colors" title="View Architecture">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-xs text-slate-500">No pending award applications.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>
@endsection
