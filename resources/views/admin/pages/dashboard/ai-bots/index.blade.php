@extends('admin.layout.dashboard')

@section('title', 'AI Bots - HaloSitek')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
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

		$activityLogs = [
			[
				'id' => 1,
				'user' => 'Alex Rivera',
				'date' => 'Oct 24, 2023',
				'prompt' => 'Generate a series of brutalist architecture poster concepts for social media campaign.',
				'status' => 'success',
				'time' => '2 min',
				'request' => 'Generate a series of brutalist architecture poster concepts for social media campaign.',
				'outputImage' => 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80',
			],
			[
				'id' => 2,
				'user' => 'Elena Vance',
				'date' => 'Oct 24, 2023',
				'prompt' => 'Abstract interpretation of digital currency flowing through a neural lattice network.',
				'status' => 'failed',
				'time' => '2 min',
				'request' => 'Abstract interpretation of digital currency flowing through a neural lattice network, hyper-realistic, 8k resolution, cinematic lighting.',
				'errorTitle' => '[CRITICAL_ERROR] Model Content Filter Triggered',
				'errorDetail' => 'Safety violation detected in prompt tokens [14, 89]. Generation halted by safety layer. Error Code: 504 Gateway Timeout (Internal Safety Denial).',
			],
			[
				'id' => 3,
				'user' => 'MarcusThorne',
				'date' => 'Oct 24, 2023',
				'prompt' => 'Auditing system security protocols for enterprise AI bot infrastructure.',
				'status' => 'success',
				'time' => '2 min',
				'request' => 'Auditing system security protocols for enterprise AI bot infrastructure.',
				'outputImage' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1200&q=80',
			],
			[
				'id' => 4,
				'user' => 'Sienna West',
				'date' => 'Oct 24, 2023',
				'prompt' => 'Write a poem about the stillness of a midnight city in rainy season.',
				'status' => 'success',
				'time' => '2 min',
				'request' => 'Write a poem about the stillness of a midnight city in rainy season.',
				'outputImage' => 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80',
			],
			[
				'id' => 5,
				'user' => 'Raisa Quinn',
				'date' => 'Oct 24, 2023',
				'prompt' => 'Create a cinematic product render of eco bottle on wet concrete floor.',
				'status' => 'failed',
				'time' => '3 min',
				'request' => 'Create a cinematic product render of eco bottle on wet concrete floor with dramatic shadows and smoke.',
				'errorTitle' => '[SYSTEM_ERROR] Diffusion Backend Unavailable',
				'errorDetail' => 'Model endpoint timeout during image synthesis stage. Retry queue failed after 3 attempts.',
			],
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

	<div class="mb-6 flex flex-wrap items-center gap-3">
		<button
			type="button"
			id="aiBotFilterAll"
			class="rounded-full bg-[#E8820C] px-8 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_0_rgba(232,130,12,0.39)] transition-all cursor-pointer"
			onclick="switchAiBotFilter('all')"
		>
			All Status
		</button>

		<button
			type="button"
			id="aiBotFilterSuccess"
			class="rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer"
			onclick="switchAiBotFilter('success')"
		>
			<span class="inline-flex items-center gap-2">
				Success
			</span>
		</button>

		<button
			type="button"
			id="aiBotFilterFailed"
			class="rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer"
			onclick="switchAiBotFilter('failed')"
		>
			<span class="inline-flex items-center gap-2">
				Failed
			</span>
		</button>
	</div>

	<div class="mb-4 flex items-center justify-between">
		<h2 class="text-xl font-black tracking-tight text-slate-900">Today Activity Logs</h2>
		<span class="text-sm font-black text-emerald-500">+120</span>
	</div>

	@component('admin.components.table', ['headers' => [
		'USER',
		['label' => 'DATE', 'class' => 'text-center'],
		'PROMPT PREVIEW',
		['label' => 'STATUS', 'class' => 'text-center'],
		['label' => 'GENERATE TIME', 'class' => 'text-center'],
		['label' => 'ACTIONS', 'class' => 'text-center']
	]])
		@foreach($activityLogs as $log)
		<tr class="group transition-colors hover:bg-slate-50" data-ai-bot-row data-status="{{ $log['status'] }}">
			<td class="whitespace-nowrap px-6 py-5">
				<div class="flex items-center gap-3">
					<img src="https://ui-avatars.com/api/?name={{ urlencode($log['user']) }}&background=ececec&color=333333&rounded=true&bold=true" class="h-10 w-10 rounded-full border border-slate-100 object-cover shadow-sm">
					<span class="text-sm font-bold text-slate-900">{{ $log['user'] }}</span>
				</div>
			</td>
			<td class="whitespace-nowrap px-6 py-5 text-center">
				<span class="rounded-md bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500">{{ $log['date'] }}</span>
			</td>
			<td class="px-6 py-5 text-sm font-medium text-slate-500">
				<span class="line-clamp-1">"{{ $log['prompt'] }}"</span>
			</td>
			<td class="whitespace-nowrap px-6 py-5 text-center">
				@if($log['status'] === 'success')
					<span class="rounded bg-emerald-50 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-600">Success</span>
				@else
					<span class="rounded bg-rose-50 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-rose-600">Failed</span>
				@endif
			</td>
			<td class="whitespace-nowrap px-6 py-5 text-center text-sm font-medium text-slate-500">{{ $log['time'] }}</td>
			<td class="whitespace-nowrap px-6 py-5 text-center">
				<button
					type="button"
					class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 cursor-pointer"
					onclick="openAiBotActionModal({{ $log['id'] }})"
					aria-label="View Action Details"
				>
					<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
						<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"></path>
					</svg>
				</button>
			</td>
		</tr>
		@endforeach
	@endcomponent
</div>

@include('admin.components.ai-bots.modal-action-detail')

<script>
	const aiBotActivityLogs = @json($activityLogs);

	function showModal(id) {
		const modal = document.getElementById(id);
		if (modal) modal.classList.remove('hidden');
	}

	function hideModal(id) {
		const modal = document.getElementById(id);
		if (modal) modal.classList.add('hidden');
	}

	document.addEventListener('click', (e) => {
		if (e.target.matches('[data-modal-backdrop]')) {
			e.target.closest('[data-modal]').classList.add('hidden');
		}

		if (e.target.closest('[data-modal-close]')) {
			e.target.closest('[data-modal]').classList.add('hidden');
		}
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			document.querySelectorAll('[data-modal]:not(.hidden)').forEach((modal) => modal.classList.add('hidden'));
		}
	});

	function switchAiBotFilter(filter) {
		const rows = document.querySelectorAll('[data-ai-bot-row]');
		const btnAll = document.getElementById('aiBotFilterAll');
		const btnSuccess = document.getElementById('aiBotFilterSuccess');
		const btnFailed = document.getElementById('aiBotFilterFailed');

		rows.forEach((row) => {
			const rowStatus = row.getAttribute('data-status');
			const isVisible = filter === 'all' || rowStatus === filter;
			row.classList.toggle('hidden', !isVisible);
		});

		btnAll.className = filter === 'all'
			? 'rounded-full bg-[#E8820C] px-8 py-2.5 text-sm font-bold text-white shadow-[0_4px_14px_0_rgba(232,130,12,0.39)] transition-all cursor-pointer'
			: 'rounded-full border border-slate-200 bg-white px-8 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer';

		btnSuccess.className = filter === 'success'
			? 'rounded-full bg-emerald-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all cursor-pointer'
			: 'rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer';

		btnFailed.className = filter === 'failed'
			? 'rounded-full bg-rose-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all cursor-pointer'
			: 'rounded-full border border-slate-200 bg-white px-6 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-50 cursor-pointer';
	}

	function openAiBotActionModal(logId) {
		const modal = document.getElementById('aiBotActionModal');
		const successPanel = modal.querySelector('[data-ai-modal-success]');
		const failurePanel = modal.querySelector('[data-ai-modal-failure]');
		const statusIcon = modal.querySelector('[data-ai-modal-status-icon]');
		const title = modal.querySelector('[data-ai-modal-title]');
		const requestText = modal.querySelector('[data-ai-modal-request]');
		const outputImage = modal.querySelector('[data-ai-modal-output-image]');
		const errorTitle = modal.querySelector('[data-ai-modal-error-title]');
		const errorDetail = modal.querySelector('[data-ai-modal-error-detail]');

		const log = aiBotActivityLogs.find((item) => item.id === logId);
		if (!log) return;

		requestText.textContent = `"${log.request || log.prompt}"`;

		if (log.status === 'success') {
			title.textContent = 'Generation Success Actions';
			statusIcon.className = 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600';
			statusIcon.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>';

			successPanel.classList.remove('hidden');
			failurePanel.classList.add('hidden');

			outputImage.src = log.outputImage || '';
			outputImage.alt = `Generated output by ${log.user}`;
		} else {
			title.textContent = 'Generation Failure Actions';
			statusIcon.className = 'inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600';
			statusIcon.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>';

			successPanel.classList.add('hidden');
			failurePanel.classList.remove('hidden');

			errorTitle.textContent = log.errorTitle || '[ERROR] Unknown issue';
			errorDetail.textContent = log.errorDetail || 'No diagnostic details were returned by the system.';
		}

		showModal('aiBotActionModal');
	}

	document.addEventListener('DOMContentLoaded', () => {
		switchAiBotFilter('all');
	});
</script>
@endsection
