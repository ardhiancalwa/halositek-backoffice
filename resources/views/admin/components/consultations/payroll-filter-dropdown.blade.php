@php
    $id = $id ?? 'payrollSubFilter';
    $name = $name ?? $id;
    $value = $value ?? 'all';
    $onChange = $onChange ?? 'switchPayrollSub(this.value)';
@endphp

<div class="relative">
    <label for="{{ $id }}" class="sr-only">Payout Filter</label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="appearance-none min-w-45 rounded-full border border-slate-200 bg-white px-4 py-2 pr-10 text-xs font-bold uppercase tracking-wide text-slate-600 shadow-sm focus:border-[#E8820C] focus:outline-none focus:ring-2 focus:ring-[#E8820C]/20 cursor-pointer"
        onchange="{{ $onChange }}"
    >
        <option value="all" {{ $value === 'all' ? 'selected' : '' }}>All</option>
        <option value="pay" {{ $value === 'pay' ? 'selected' : '' }}>Pay</option>
        <option value="history" {{ $value === 'history' ? 'selected' : '' }}>History</option>
    </select>

    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>
