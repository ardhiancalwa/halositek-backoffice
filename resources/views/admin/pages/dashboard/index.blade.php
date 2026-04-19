@extends('admin.layout.dashboard')

@section('title', 'Dashboard - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-12">

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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
        <section class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">User Growth</p>
                    <div class="mt-3 flex items-end gap-3">
                        <h2 id="user-growth-total" class="text-4xl font-bold tracking-tight text-slate-900">0</h2>
                        <p id="user-growth-caption" class="pb-1 text-sm text-slate-500">new users in the last 7 days</p>
                    </div>
                </div>

                <div class="inline-flex rounded-full bg-slate-100 p-1">
                    <button
                        type="button"
                        class="user-growth-period rounded-full px-4 py-2 text-sm font-semibold text-slate-500 transition data-[active=true]:bg-white data-[active=true]:text-[#E8820C] data-[active=true]:shadow-sm"
                        data-period="7d"
                        data-active="true"
                    >
                        Last 7 days
                    </button>
                    <button
                        type="button"
                        class="user-growth-period rounded-full px-4 py-2 text-sm font-semibold text-slate-500 transition data-[active=true]:bg-white data-[active=true]:text-[#E8820C] data-[active=true]:shadow-sm"
                        data-period="30d"
                        data-active="false"
                    >
                        Last 30 days
                    </button>
                </div>
            </div>

            <div class="mt-6 rounded-[28px] bg-gradient-to-b from-orange-50 via-white to-white p-4 md:p-6">
                <div class="relative h-72">
                    <svg id="user-growth-chart" class="h-full w-full" viewBox="0 0 640 240" preserveAspectRatio="none" role="img" aria-label="New user registrations over time"></svg>
                    <div id="user-growth-empty" class="absolute inset-0 hidden items-center justify-center text-sm text-slate-400">
                        No user registrations found for this period.
                    </div>
                </div>
                <div id="user-growth-labels" class="mt-4 grid gap-2 text-xs font-medium text-slate-400"></div>
            </div>
        </section>

        <aside class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">Quick Insight</p>
            <h3 class="mt-3 text-2xl font-bold tracking-tight text-slate-900">Newest account activity</h3>
            <p class="mt-3 text-sm leading-6 text-slate-500">
                This chart tracks how many users were created each day, so we can spot spikes in acquisition and compare weekly momentum against a wider 30-day trend.
            </p>

            <div class="mt-6 space-y-4">
                <div class="rounded-2xl border border-orange-100 bg-orange-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#E8820C]">Best day</p>
                    <p id="user-growth-peak" class="mt-2 text-3xl font-bold text-slate-900">0</p>
                    <p class="mt-1 text-sm text-slate-500">highest number of newly created users in a single day</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Available periods</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Use 7 days for short-term movement and 30 days for a broader monthly trend without overcrowding the dashboard.
                    </p>
                </div>
            </div>
        </aside>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const userGrowthElements = {
        chart: document.getElementById('user-growth-chart'),
        empty: document.getElementById('user-growth-empty'),
        total: document.getElementById('user-growth-total'),
        caption: document.getElementById('user-growth-caption'),
        labels: document.getElementById('user-growth-labels'),
        peak: document.getElementById('user-growth-peak'),
        buttons: Array.from(document.querySelectorAll('.user-growth-period')),
    };

    const userGrowthState = {
        period: '7d',
    };

    const buildChartPath = (values, width, height, padding) => {
        if (!values.length) {
            return '';
        }

        if (values.length === 1) {
            const centerY = height / 2;
            return `M ${padding} ${centerY} L ${width - padding} ${centerY}`;
        }

        const minValue = Math.min(...values);
        const maxValue = Math.max(...values);
        const range = Math.max(maxValue - minValue, 1);
        const chartWidth = width - (padding * 2);
        const chartHeight = height - (padding * 2);

        return values
            .map((value, index) => {
                const x = padding + (chartWidth * index) / (values.length - 1);
                const normalized = (value - minValue) / range;
                const y = height - padding - (normalized * chartHeight);

                return `${index === 0 ? 'M' : 'L'} ${x.toFixed(2)} ${y.toFixed(2)}`;
            })
            .join(' ');
    };

    const buildAreaPath = (linePath, values, width, height, padding) => {
        if (!linePath || !values.length) {
            return '';
        }

        const chartWidth = width - (padding * 2);
        const lastX = values.length === 1 ? width - padding : padding + chartWidth;

        return `${linePath} L ${lastX.toFixed(2)} ${(height - padding).toFixed(2)} L ${padding} ${(height - padding).toFixed(2)} Z`;
    };

    const renderUserGrowthChart = (dataset) => {
        const width = 640;
        const height = 240;
        const padding = 24;
        const labels = Array.isArray(dataset?.chart?.labels) ? dataset.chart.labels : [];
        const values = Array.isArray(dataset?.chart?.values) ? dataset.chart.values.map(Number) : [];
        const totalNewUsers = Number(dataset?.summary?.total_new_users ?? 0);
        const peakNewUsers = Number(dataset?.summary?.peak_new_users ?? 0);
        const linePath = buildChartPath(values, width, height, padding);
        const areaPath = buildAreaPath(linePath, values, width, height, padding);
        const hasActivity = values.some((value) => value > 0);

        userGrowthElements.total.textContent = totalNewUsers.toLocaleString();
        userGrowthElements.peak.textContent = peakNewUsers.toLocaleString();
        userGrowthElements.caption.textContent = userGrowthState.period === '30d'
            ? 'new users in the last 30 days'
            : 'new users in the last 7 days';

        userGrowthElements.empty.classList.toggle('hidden', hasActivity);
        userGrowthElements.empty.classList.toggle('flex', !hasActivity);

        userGrowthElements.chart.innerHTML = `
            <defs>
                <linearGradient id="user-growth-fill" x1="0%" x2="0%" y1="0%" y2="100%">
                    <stop offset="0%" stop-color="#E8820C" stop-opacity="0.28"></stop>
                    <stop offset="100%" stop-color="#E8820C" stop-opacity="0"></stop>
                </linearGradient>
            </defs>
            <g stroke="#E2E8F0" stroke-width="1">
                <line x1="${padding}" y1="${height - padding}" x2="${width - padding}" y2="${height - padding}"></line>
                <line x1="${padding}" y1="${padding}" x2="${padding}" y2="${height - padding}"></line>
            </g>
            ${hasActivity ? `<path d="${areaPath}" fill="url(#user-growth-fill)"></path>` : ''}
            <path d="${linePath}" fill="none" stroke="#E8820C" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
        `;

        userGrowthElements.labels.style.gridTemplateColumns = `repeat(${Math.max(labels.length, 1)}, minmax(0, 1fr))`;
        userGrowthElements.labels.innerHTML = labels
            .map((label) => `<span class="text-center">${label}</span>`)
            .join('');
    };

    const loadUserGrowth = async (period) => {
        userGrowthState.period = period;
        userGrowthElements.buttons.forEach((button) => {
            button.dataset.active = button.dataset.period === period ? 'true' : 'false';
        });

        try {
            const response = await fetch(`${@js(route('dashboard.user-growth'))}?period=${period}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            renderUserGrowthChart(payload?.data ?? {});
        } catch (error) {
            console.error('Error fetching user growth data:', error);
        }
    };

    userGrowthElements.buttons.forEach((button) => {
        button.addEventListener('click', () => loadUserGrowth(button.dataset.period ?? '7d'));
    });

    try {
        const response = await fetch(@js(route('dashboard.stats')), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const stats = payload?.data ?? {};

        document.getElementById('total-users').textContent = Number(stats.total_users ?? 0).toLocaleString();
        document.getElementById('total-architects').textContent = Number(stats.total_architects ?? 0).toLocaleString();
        document.getElementById('total-designs').textContent = Number(stats.total_designs ?? 0).toLocaleString();
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }

    await loadUserGrowth(userGrowthState.period);
});
</script>
@endpush

@endsection
