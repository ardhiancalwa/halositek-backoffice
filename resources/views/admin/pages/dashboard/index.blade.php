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
});
</script>
@endpush

@endsection
