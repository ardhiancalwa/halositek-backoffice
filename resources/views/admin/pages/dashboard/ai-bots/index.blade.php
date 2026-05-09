@extends('admin.layout.dashboard')

@section('title', 'AI Bots - HaloSitek')

@section('content')
<div id="ai-bots-wrapper"
     class="max-w-7xl mx-auto pb-12"
     data-logs-url="{{ route('admin.dashboard.ai-bots.logs-data') }}"
>
	<div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-600">
		<span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
		Bot Status: Active
	</div>

	<h1 class="mb-6 text-3xl font-black tracking-tight text-slate-900">Real-time Performance</h1>

	@php
		$metrics = [
			['title' => 'Total Generates', 'value' => '142.832', 'bar' => 'bg-[#E8820C]', 'width' => '72%'],
			['title' => 'Total Success', 'value' => '142.548', 'bar' => 'bg-emerald-500', 'width' => '91%'],
			['title' => 'System Failures', 'value' => '284', 'bar' => 'bg-rose-500', 'width' => '20%'],
		];
	@endphp

	<div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
		@foreach($metrics as $metric)
		<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
			<div class="mb-4 inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-500">
				<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"></path>
				</svg>
			</div>
			<p class="mb-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{{ $metric['title'] }}</p>
			<h3 class="mb-4 text-4xl font-black tracking-tight text-slate-900">{{ $metric['value'] }}</h3>
			<div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
				<div class="h-full rounded-full {{ $metric['bar'] }}" style="width: {{ $metric['width'] }}"></div>
			</div>
		</div>
		@endforeach
	</div>

	<!-- Filter Buttons -->
	<div class="mb-6 flex flex-wrap items-center gap-3">
		<button
			type="button"
			data-filter="all"
			class="ai-bot-filter-btn rounded-full bg-[#E8820C] px-8 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_0_rgba(232,130,12,0.39)] transition-all cursor-pointer"
			onclick="switchAiBotFilter('all')"
		>
			All Status
		</button>

		<button
			type="button"
			data-filter="success"
			class="ai-bot-filter-btn rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer"
			onclick="switchAiBotFilter('success')"
		>
			Success
		</button>

		<button
			type="button"
			data-filter="failed"
			class="ai-bot-filter-btn rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer"
			onclick="switchAiBotFilter('failed')"
		>
			Failed
		</button>
	</div>

	<!-- Activity Logs Header -->
	<div class="mb-4 flex items-center justify-between">
		<h2 class="text-xl font-black tracking-tight text-slate-900">Today Activity Logs</h2>
		<span class="text-sm font-black text-emerald-500">+120</span>
	</div>

	<!-- Activity Logs Table -->
	@component('admin.components.table', ['headers' => [
		'USER',
		['label' => 'DATE', 'class' => 'text-center'],
		'PROMPT PREVIEW',
		['label' => 'STATUS', 'class' => 'text-center'],
		['label' => 'GENERATE TIME', 'class' => 'text-center'],
		['label' => 'ACTIONS', 'class' => 'text-center']
	], 'tbodyId' => 'ai-bots-table-body'])
		<tr>
			<td colspan="6" class="px-6 py-12 text-center text-slate-500">
				<div class="flex items-center justify-center gap-3">
					<svg class="animate-spin h-5 w-5 text-[#E8820C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<span class="text-sm font-medium text-slate-400">Loading data...</span>
				</div>
			</td>
		</tr>
	@endcomponent

	<!-- Pagination -->
	@component('admin.components.pagination-footer', [
		'currentPage' => 1,
		'totalPages' => 1,
		'previousDisabled' => true,
		'nextDisabled' => false,
		'currentPageId' => 'ai-bots-current-page',
		'totalPagesId' => 'ai-bots-total-pages',
		'prevButtonId' => 'ai-bots-prev-page',
		'nextButtonId' => 'ai-bots-next-page',
		'numbersId' => 'ai-bots-pagination-numbers',
	])
	@endcomponent
</div>

@include('admin.components.ai-bots.modal-action-detail')

@push('scripts')
<script src="{{ asset('js/admin/pages/dashboard/ai-bots/index.js') }}?v={{ filemtime(public_path('js/admin/pages/dashboard/ai-bots/index.js')) }}"></script>
@endpush
@endsection
