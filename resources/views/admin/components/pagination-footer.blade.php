@php
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
    $pageLinks = $pageLinks ?? [];
    $previousUrl = $previousUrl ?? null;
    $nextUrl = $nextUrl ?? null;
    $previousDisabled = $previousDisabled ?? false;
    $nextDisabled = $nextDisabled ?? false;
    $wrapperClass = $wrapperClass ?? '';
    $footerClass = $footerClass ?? '';
    $currentPageId = $currentPageId ?? null;
    $totalPagesId = $totalPagesId ?? null;
    $prevButtonId = $prevButtonId ?? null;
    $nextButtonId = $nextButtonId ?? null;
    $numbersId = $numbersId ?? null;
@endphp

<div class="{{ $wrapperClass }}">
    <div class="dashboard-table-footer {{ $footerClass }}">
        <div class="dashboard-table-footer-text text-sm">
            Showing
            <span @if ($currentPageId) id="{{ $currentPageId }}" @endif>{{ $currentPage }}</span>
            of
            <span @if ($totalPagesId) id="{{ $totalPagesId }}" @endif>{{ $totalPages }}</span>
            pages
        </div>
        <div class="dashboard-pagination">
            @if ($previousUrl !== null)
                <a
                    href="{{ $previousDisabled ? '#' : $previousUrl }}"
                    @if ($previousDisabled) aria-disabled="true" @endif
                    class="dashboard-pagination-button inline-flex items-center justify-center {{ $previousDisabled ? 'pointer-events-none' : '' }}"
                >
                    &larr;
                </a>
            @else
                <button
                    type="button"
                    @if ($prevButtonId) id="{{ $prevButtonId }}" @endif
                    class="dashboard-pagination-button"
                    @if ($previousDisabled) disabled @endif
                >
                    &larr;
                </button>
            @endif

            <div @if ($numbersId) id="{{ $numbersId }}" @endif class="dashboard-pagination-numbers">
                @foreach ($pageLinks as $pageLink)
                    @if (! empty($pageLink['url']))
                        <a
                            href="{{ $pageLink['url'] }}"
                            class="dashboard-pagination-number inline-flex items-center justify-center {{ ! empty($pageLink['isActive']) ? 'is-active' : '' }}"
                        >
                            {{ $pageLink['label'] }}
                        </a>
                    @else
                        <button
                            type="button"
                            class="dashboard-pagination-number text-sm font-medium {{ ! empty($pageLink['isActive']) ? 'is-active' : '' }}"
                        >
                            {{ $pageLink['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>

            @if ($nextUrl !== null)
                <a
                    href="{{ $nextDisabled ? '#' : $nextUrl }}"
                    @if ($nextDisabled) aria-disabled="true" @endif
                    class="dashboard-pagination-button inline-flex items-center justify-center {{ $nextDisabled ? 'pointer-events-none' : '' }}"
                >
                    &rarr;
                </a>
            @else
                <button
                    type="button"
                    @if ($nextButtonId) id="{{ $nextButtonId }}" @endif
                    class="dashboard-pagination-button"
                    @if ($nextDisabled) disabled @endif
                >
                    &rarr;
                </button>
            @endif
        </div>
    </div>
</div>
