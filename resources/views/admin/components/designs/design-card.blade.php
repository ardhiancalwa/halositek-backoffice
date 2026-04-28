@php
    $images = $project->images;
    if (is_string($images)) {
        $images = json_decode($images, true) ?: [];
    }

    if (! is_array($images)) {
        $images = [];
    }

    $image = collect($images)->first(fn ($value) => is_string($value) && filled($value));
    $imageUrl = null;

    if (is_string($image) && filled($image)) {
        $imageUrl = str_starts_with($image, 'http://') || str_starts_with($image, 'https://')
            ? $image
            : \Illuminate\Support\Facades\Storage::url($image);
    }

    $styleValue = $project->style?->value ?? $project->style;
    $style = filled($styleValue) ? strtoupper((string) $styleValue) : 'UNTITLED';
    $architectName = $project->architect?->name;

    $meta = collect([
        filled($project->area) ? (string) $project->area : null,
        $architectName ? 'by '.$architectName : null,
    ])->filter()->implode(' - ');
@endphp

<article class="group overflow-hidden rounded-2xl border border-[#EEF1F5] bg-white shadow-[0_12px_30px_-26px_rgba(15,23,42,0.55)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-26px_rgba(15,23,42,0.65)]">
    <div class="relative h-44 overflow-hidden bg-[linear-gradient(135deg,#fff7ed_0%,#ffedd5_35%,#fdba74_100%)]">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $project->name }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
            >
        @else
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.9),rgba(255,255,255,0)_42%),linear-gradient(135deg,#fb923c_0%,#f97316_45%,#7c2d12_100%)]"></div>
            <div class="absolute inset-x-0 bottom-0 h-16 bg-[linear-gradient(180deg,rgba(255,255,255,0)_0%,rgba(15,23,42,0.08)_100%)]"></div>
            <div class="absolute inset-x-6 bottom-5 rounded-2xl bg-white/16 p-4 backdrop-blur-sm ring-1 ring-white/30">
                <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-white/75">{{ $style }}</p>
                <h3 class="mt-2 text-lg font-semibold tracking-tight text-white">{{ $project->name }}</h3>
            </div>
        @endif

        <span class="absolute right-3 top-3 rounded-full bg-white/92 px-3 py-1 text-[10px] font-bold tracking-[0.24em] text-slate-900 shadow-sm">
            {{ $style }}
        </span>
    </div>

    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#E8820C]">{{ $style }}</p>
                <h2 class="mt-2 text-[15px] font-bold leading-5 text-slate-900">{{ $project->name }}</h2>
            </div>

            <div class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                {{ number_format((int) ($project->likes_count ?? 0)) }} likes
            </div>
        </div>

        <p class="mt-3 min-h-10 text-xs font-medium leading-5 text-slate-400">
            {{ $meta ?: 'Area and architect details will appear here once available.' }}
        </p>

        @if (filled($project->estimated_cost))
            <p class="mt-3 text-sm font-semibold text-slate-700">{{ $project->estimated_cost }}</p>
        @endif

        <a
            href="{{ route('admin.dashboard.designs.show', $project) }}"
            class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-100 bg-white py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
            Manage
        </a>
    </div>
</article>
