@php
    $navigationSections = [
        [
            'title' => null,
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'match' => ['dashboard', 'dashboard.*'],
                    'icon' => 'dashboard',
                ],
            ],
        ],
        [
            'title' => 'Management',
            'items' => [
                [
                    'label' => 'User',
                    'route' => 'users.index',
                    'match' => ['users.*'],
                    'icon' => 'user',
                ],
                [
                    'label' => 'Architect',
                    'route' => 'architects.index',
                    'match' => ['architects.*'],
                    'icon' => 'architect',
                ],
                [
                    'label' => 'Design',
                    'route' => 'designs.index',
                    'match' => ['designs.*'],
                    'icon' => 'design',
                ],
            ],
        ],
        [
            'title' => 'Services',
            'items' => [
                [
                    'label' => 'Consultations',
                    'route' => 'consultations.index',
                    'match' => ['consultations.*'],
                    'icon' => 'consultation',
                ],
                [
                    'label' => 'AI Bots',
                    'route' => 'ai-bots.index',
                    'match' => ['ai-bots.*'],
                    'icon' => 'bot',
                ],
            ],
        ],
    ];

    $renderSidebarIcon = function (string $icon, bool $isActive): string {
        $iconName = $icon;
        if (in_array($icon, ['user', 'architect', 'design']) && $isActive) {
            $iconName .= '-orange';
        }
        
        return match ($icon) {
            'dashboard' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"></rect></svg>',
            'user', 'architect', 'design', 'consultation' => '<img src="' . asset("images/dashboard/{$iconName}-icon.png") . '" class="h-5 w-5" alt="'.ucfirst($icon).'">',
            'bot' => '<img src="' . asset("images/dashboard/ai-icon.png") . '" class="h-5 w-5" alt="AI Bot">',
            default => '',
        };
    };

    $sidebarUserName = auth()->user()->name ?? 'Alex Thompson';
    $sidebarUserRole = data_get(auth()->user(), 'role', 'Super Admin');
    $sidebarUserInitial = strtoupper(substr($sidebarUserName, 0, 1));
@endphp

<aside
    class="hidden h-screen w-72 shrink-0 border-r border-slate-200 bg-white md:flex md:flex-col"
    data-sidebar-auth
    data-logout-url="{{ url('/api/v1/logout') }}"
    data-login-url="{{ route('login') }}"
>
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex h-20 items-center px-6">
            <img src="{{ asset('images/brand.png') }}" class="h-9 w-auto" alt="HaloSitek">
        </div>

        <nav class="flex-1 overflow-y-auto px-4 pb-6">
            @foreach ($navigationSections as $section)
                <div class="{{ $loop->first ? 'space-y-1' : 'mt-6 space-y-1' }}">
                    @if ($section['title'])
                        <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            {{ $section['title'] }}
                        </p>
                    @endif

                    @foreach ($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs(...$item['match']);
                            $routeExists = \Illuminate\Support\Facades\Route::has($item['route']);
                            $href = $routeExists ? route($item['route']) : '#';
                            $stateClasses = $isActive
                                ? 'bg-[#E8820C] text-white shadow-[0_10px_30px_-18px_rgba(232,130,12,0.95)]'
                                : 'text-slate-500 hover:bg-[#FFF5EA] hover:text-[#E8820C]';
                            $iconMarkup = $renderSidebarIcon($item['icon'], $isActive);
                        @endphp

                        <a
                            href="{{ $href }}"
                            @unless($routeExists) aria-disabled="true" @endunless
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ $stateClasses }}"
                        >
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/15' : 'bg-slate-50 text-slate-400 group-hover:bg-white group-hover:text-[#E8820C]' }}">
                                {!! $iconMarkup !!}
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </div>

    <div class="border-t border-slate-200 p-4">
        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-3">
            <div class="flex min-w-0 items-center gap-3">
                <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full bg-slate-200">
                    <img
                        src="https://ui-avatars.com/api/?name={{ urlencode($sidebarUserName) }}&background=F1F5F9&color=475569&size=80"
                        alt="{{ $sidebarUserName }}"
                        class="h-full w-full object-cover"
                        data-sidebar-avatar
                    >
                    <div
                        class="absolute inset-0 flex items-center justify-center bg-slate-200 text-sm font-semibold text-slate-600"
                        data-sidebar-avatar-fallback
                    >
                        {{ $sidebarUserInitial }}
                    </div>
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900" data-sidebar-name>{{ $sidebarUserName }}</p>
                    <p class="truncate text-xs capitalize text-slate-500" data-sidebar-role>{{ $sidebarUserRole }}</p>
                </div>
            </div>

            <button
                type="button"
                class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-white hover:text-slate-600 disabled:cursor-not-allowed disabled:opacity-60"
                data-sidebar-logout
                aria-label="Logout"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <path d="M16 17l5-5-5-5"></path>
                    <path d="M21 12H9"></path>
                </svg>
            </button>
        </div>
    </div>
</aside>
