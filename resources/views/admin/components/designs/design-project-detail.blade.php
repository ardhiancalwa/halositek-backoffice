@php
    use App\Enums\ProjectStyle;
    use Illuminate\Support\Facades\Storage;

    $resolveMediaUrls = static function (mixed $media): array {
        $items = $media;

        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(fn ($value) => is_string($value) && filled($value))
            ->map(function (string $value): string {
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }

                return Storage::url($value);
            })
            ->values()
            ->all();
    };

    $imageUrls = $resolveMediaUrls($project->images);
    $layoutImageUrls = $resolveMediaUrls($project->layout_images);
    $heroImage = $imageUrls[0] ?? null;
    $styleValue = $project->style?->value ?? $project->style;
    $styleLabel = filled($styleValue) ? ucfirst((string) $styleValue) : 'Not set';
    $architect = $project->architect;
    $architectName = $architect?->name ?: 'Unassigned architect';
    $architectInitials = collect(explode(' ', trim($architectName)))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $uploadedAt = $project->created_at?->format('Y/m/d') ?? '-';
    $description = filled($project->description) ? $project->description : 'No project description has been added yet.';
    $likeCount = number_format((int) ($project->likes_count ?? 0));
    $styleOptions = ProjectStyle::cases();
    $titleValue = old('name', $project->name ?? '');
    $descriptionValue = old('description', $project->description ?? '');
@endphp

<div class="space-y-7">
    <section class="rounded-[28px] border border-[#EEF1F5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.45)] sm:p-7">
        <div class="flex items-center gap-2 text-[#E8820C]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7h8" />
                <path d="M4 12h5" />
                <path d="M4 17h3" />
                <path d="M15 8l5 4-5 4" />
            </svg>
            <h2 class="text-xl font-bold tracking-tight">Information</h2>
        </div>

        <div class="mt-5 border-t border-slate-100 pt-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_280px]">
                <div class="space-y-4">
                    <div>
                        <div class="flex flex-col">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">Project Title</p>
                                <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">Upload Date</p>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex w-3/4 items-center justify-between rounded-lg border border-slate-300 bg-white px-4 py-2 hover:border-[#D97706] focus-within:border-[#D97706] focus-within:ring-1 focus-within:ring-[#D97706]">
                                    <input
                                        type="text"
                                        name="name"
                                        maxlength="64"
                                        data-char-input
                                        data-char-max="64"
                                        class="w-full border-0 bg-transparent text-sm font-semibold text-slate-900 outline-none focus:ring-0"
                                        value="{{ $titleValue }}"
                                    >
                                    <span class="ml-2 text-xs font-medium text-slate-400" data-char-count>{{ str_pad((string) strlen($titleValue), 2, '0', STR_PAD_LEFT) }}/64</span>
                                </div>
                                <div class="flex w-[22%] items-center justify-center">
                                    <p class="text-sm font-semibold tracking-tight text-slate-900">{{ $uploadedAt }}</p>
                                </div>
                            </div>
                            @error('name')
                                <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">Architect</p>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded bg-orange-100 text-xs font-bold text-[#E8820C]">
                                {{ $architectInitials ?: 'NA' }}
                            </div>
                            <p class="text-sm font-semibold text-slate-900">{{ $architectName }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <div class="flex flex-col">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-500">Description</p>
                    <div class="mt-2 flex justify-between rounded-lg border border-slate-300 bg-white px-4 py-3 hover:border-[#D97706] focus-within:border-[#D97706] focus-within:ring-1 focus-within:ring-[#D97706]">
                        <textarea name="description" maxlength="255" data-char-input data-char-max="255" class="w-full resize-none border-0 bg-transparent text-sm font-medium leading-relaxed text-slate-700 outline-none focus:ring-0" rows="3">{{ $descriptionValue }}</textarea>
                        <span class="ml-4 mt-auto text-xs font-medium text-slate-400" data-char-count>{{ str_pad((string) strlen($descriptionValue), 2, '0', STR_PAD_LEFT) }}/255</span>
                    </div>
                    @error('description')
                        <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[28px] border border-[#EEF1F5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.45)] sm:p-7">
        <div class="flex items-center gap-2 text-[#E8820C]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="2" />
                <path d="M8 5v14" />
            </svg>
            <h2 class="text-xl font-bold tracking-tight">Specifications</h2>
        </div>

        <div class="mt-5 border-t border-slate-100 pt-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-stretch">
                <div class="w-full flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 cursor-pointer hover:border-[#D97706] focus-within:border-[#D97706] focus-within:ring-1 focus-within:ring-[#D97706]">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">Architectural Style</p>
                    <div class="mt-1 flex items-center justify-between">
                        <select name="style" class="w-full appearance-none border-0 bg-transparent px-0 text-sm font-semibold tracking-tight text-slate-900 outline-none focus:ring-0">
                            @foreach ($styleOptions as $styleOption)
                                <option value="{{ $styleOption->value }}" @selected(old('style', $styleValue) === $styleOption->value)>
                                    {{ ucfirst($styleOption->value) }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="w-full flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 hover:border-[#D97706] focus-within:border-[#D97706] focus-within:ring-1 focus-within:ring-[#D97706]">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">Area</p>
                    <input type="text" name="area" class="mt-1 w-full border-0 bg-transparent px-0 text-sm font-semibold tracking-tight text-slate-900 outline-none focus:ring-0" value="{{ old('area', $project->area) }}">
                </div>

                <div class="w-full flex-[1.5] rounded-lg border border-slate-300 bg-white px-4 py-3 hover:border-[#D97706] focus-within:border-[#D97706] focus-within:ring-1 focus-within:ring-[#D97706]">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">Highlight Features</p>
                    <input type="text" name="highlight_features" class="mt-1 w-full border-0 bg-transparent px-0 text-sm font-semibold tracking-tight text-slate-900 outline-none focus:ring-0" value="{{ old('highlight_features', $project->highlight_features) }}">
                </div>

                <div class="flex w-32 flex-col justify-center px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">Total Like</p>
                    <p class="mt-1 text-base font-bold tracking-tight text-slate-900">{{ $likeCount }}</p>
                </div>
            </div>

            <div class="mt-4 rounded-lg border border-[#D97706] bg-[#FFFBF5] px-4 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400">Estimated Cost</p>
                    <input
                        type="text"
                        name="estimated_cost"
                        class="w-56 border-0 bg-transparent p-0 text-right text-sm font-bold tracking-tight text-[#D97706] outline-none focus:ring-0"
                        value="{{ old('estimated_cost', $project->estimated_cost) }}"
                    >
                </div>
            </div>

            @foreach (['style', 'area', 'highlight_features', 'estimated_cost'] as $field)
                @error($field)
                    <p class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p>
                @enderror
            @endforeach
        </div>
    </section>

    <section class="rounded-[28px] border border-[#EEF1F5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.45)] sm:p-7">
        <div class="flex items-center gap-2 text-[#E8820C]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <path d="M21 15l-5-5L5 21" />
            </svg>
            <h2 class="text-xl font-bold tracking-tight">Media Design</h2>
        </div>

        <div class="mt-6">
            @if ($heroImage)
                <div class="relative group">
                    <img
                        src="{{ $heroImage }}"
                        alt="{{ $project->name }} main design"
                        class="h-[360px] w-full rounded-2xl object-cover"
                    >
                    <button type="button" class="absolute bottom-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-white text-red-500 shadow-md transition hover:bg-red-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                        </svg>
                    </button>
                </div>
            @else
                <div class="flex h-[360px] w-full items-center justify-center rounded-2xl bg-slate-100 text-sm font-medium text-slate-400">
                    No media design uploaded
                </div>
            @endif

            <div class="mt-4 grid gap-4 grid-cols-2 md:grid-cols-4">
                @if (count($imageUrls) > 1)
                    @foreach (array_slice($imageUrls, 1) as $mediaUrl)
                        <div class="relative group h-48">
                            <img
                                src="{{ $mediaUrl }}"
                                alt="{{ $project->name }} media {{ $loop->iteration + 1 }}"
                                class="h-full w-full rounded-2xl object-cover"
                            >
                            <button type="button" class="absolute bottom-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-500 shadow-sm transition hover:bg-red-50">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @endif
                <label for="design-images-input" class="flex h-48 cursor-pointer flex-col items-center justify-center space-y-2 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 transition hover:border-[#D97706] hover:text-[#D97706] hover:bg-[#FFFBF5]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span class="text-xs font-bold uppercase tracking-wider">Add</span>
                </label>
            </div>
            <input id="design-images-input" type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple class="hidden">
            @error('images')
                <p class="mt-3 text-xs font-medium text-red-500">{{ $message }}</p>
            @enderror
            @error('images.*')
                <p class="mt-3 text-xs font-medium text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="rounded-[28px] border border-[#EEF1F5] bg-white p-6 shadow-[0_18px_40px_-32px_rgba(15,23,42,0.45)] sm:p-7">
        <div class="flex items-center gap-2 text-[#E8820C]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 7h18" />
                <path d="M3 12h18" />
                <path d="M3 17h18" />
                <path d="M8 3v18" />
                <path d="M16 3v18" />
            </svg>
            <h2 class="text-xl font-bold tracking-tight">Layout Design</h2>
        </div>

        <div class="mt-6 grid gap-4 grid-cols-2 md:grid-cols-4">
            @forelse ($layoutImageUrls as $layoutUrl)
                <div class="relative group h-64">
                    <img
                        src="{{ $layoutUrl }}"
                        alt="{{ $project->name }} layout {{ $loop->iteration }}"
                        class="h-full w-full rounded-2xl object-cover border border-slate-200"
                    >
                    <button type="button" class="absolute bottom-3 right-3 flex h-7 w-7 items-center justify-center rounded-full bg-white text-red-500 shadow-sm transition hover:bg-red-50">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4" />
                        </svg>
                    </button>
                </div>
            @empty
                <div class="col-span-full flex h-64 items-center justify-center rounded-2xl bg-slate-100 text-sm font-medium text-slate-400">
                    No layout design uploaded
                </div>
            @endforelse
            <label for="layout-images-input" class="flex h-64 cursor-pointer flex-col items-center justify-center space-y-2 rounded-2xl border-2 border-dashed border-slate-300 text-slate-500 transition hover:border-[#D97706] hover:text-[#D97706] hover:bg-[#FFFBF5]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span class="text-xs font-bold uppercase tracking-wider">Add</span>
            </label>
        </div>
        <input id="layout-images-input" type="file" name="layout_images[]" accept=".jpg,.jpeg,.png,.webp" multiple class="hidden">
        @error('layout_images')
            <p class="mt-3 text-xs font-medium text-red-500">{{ $message }}</p>
        @enderror
        @error('layout_images.*')
            <p class="mt-3 text-xs font-medium text-red-500">{{ $message }}</p>
        @enderror
    </section>
</div>
