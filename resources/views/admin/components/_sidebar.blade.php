@php
    $navigationItems = [
        [
            'type' => 'link',
            'label' => 'Dashboard',
            'route' => 'admin.dashboard.index',
            'match' => ['admin.dashboard.index'],
            'icon' => 'dashboard',
        ],
        [
            'type' => 'dropdown',
            'label' => 'Management',
            'children' => [
                [
                    'label' => 'User',
                    'route' => 'admin.dashboard.users.index',
                    'match' => ['admin.dashboard.users.*'],
                    'icon' => 'user',
                ],
                [
                    'label' => 'Architect',
                    'route' => 'admin.dashboard.architects.index',
                    'match' => ['admin.dashboard.architects.*'],
                    'icon' => 'architect',
                ],
                
            ],
        ],
        [
            'type' => 'link',
            'label' => 'Design',
            'route' => 'admin.dashboard.designs.index',
            'match' => ['admin.dashboard.designs.*'],
            'icon' => 'design',
        ],
        [
            'type' => 'dropdown',
            'label' => 'Service',
            'children' => [
                [
                    'label' => 'Consultations',
                    'route' => 'admin.dashboard.consultations.index',
                    'match' => ['admin.dashboard.consultations.*'],
                    'icon' => 'consultation',
                ],
                [
                    'label' => 'AI Bots',
                    'route' => 'admin.dashboard.ai-bots.index',
                    'match' => ['admin.dashboard.ai-bots.*'],
                    'icon' => 'bot',
                ],
            ],
        ],
    ];

    $renderSidebarIcon = function (string $icon, bool $isActive): string {
        if ($icon === 'user') {
            return '
                <span class="sidebar-user-icon-wrap relative block h-5 w-5">
                    <img src="' . asset('images/dashboard/user-icon.png') . '" class="sidebar-user-icon sidebar-user-icon-default absolute inset-0 h-5 w-5" alt="User">
                    <img src="' . asset('images/dashboard/user-icon-orange.png') . '" class="sidebar-user-icon sidebar-user-icon-hover absolute inset-0 h-5 w-5" alt="User">
                    <img src="' . asset('images/dashboard/user-icon-white.png') . '" class="sidebar-user-icon sidebar-user-icon-active absolute inset-0 h-5 w-5" alt="User">
                </span>
            ';
        }

        $iconName = $icon;
        if (in_array($icon, ['architect', 'design'], true) && $isActive) {
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

    // Check if any child in a dropdown is active
    $isDropdownActive = function (array $children): bool {
        foreach ($children as $child) {
            if (request()->routeIs(...$child['match'])) {
                return true;
            }
        }
        return false;
    };
@endphp

<aside
    class="hidden h-screen w-64 shrink-0 border-r border-slate-200 bg-white md:flex md:flex-col"
    data-sidebar-auth
    data-logout-url="{{ url('/api/v1/logout') }}"
    data-login-url="{{ route('admin.auth.login') }}"
>
    <div class="flex min-h-0 flex-1 flex-col">
        {{-- Brand --}}
        <div class="flex h-20 items-center px-5">
            <img src="{{ asset('images/brand.png') }}" class="h-9 w-auto" alt="HaloSitek">
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 pb-6 pt-2">
            @foreach ($navigationItems as $navItem)
                @if ($navItem['type'] === 'link')
                    {{-- Single link item --}}
                    @php
                        $isActive = request()->routeIs(...$navItem['match']);
                        $routeExists = \Illuminate\Support\Facades\Route::has($navItem['route']);
                        $href = $routeExists ? route($navItem['route']) : '#';
                        $iconMarkup = $renderSidebarIcon($navItem['icon'], $isActive);
                    @endphp

                    <a
                        href="{{ $href }}"
                        @unless($routeExists) aria-disabled="true" @endunless
                        class="sidebar-nav-link group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ $isActive ? 'is-active' : '' }} {{ $isActive ? 'bg-[#E8820C] text-white shadow-[0_10px_30px_-18px_rgba(232,130,12,0.95)]' : 'text-slate-500 hover:bg-[#FFF5EA] hover:text-[#E8820C]' }}"
                    >
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isActive ? 'bg-white/15' : 'bg-slate-50 text-slate-400 group-hover:bg-white group-hover:text-[#E8820C]' }}">
                            {!! $iconMarkup !!}
                        </span>
                        <span>{{ $navItem['label'] }}</span>
                    </a>

                @elseif ($navItem['type'] === 'dropdown')
                    {{-- Dropdown section --}}
                    @php
                        $dropdownId = \Illuminate\Support\Str::slug($navItem['label']);
                        $hasActiveChild = $isDropdownActive($navItem['children']);
                    @endphp

                    <div class="mt-4" data-sidebar-dropdown data-dropdown-id="{{ $dropdownId }}">
                        {{-- Dropdown toggle --}}
                        <button
                            type="button"
                            class="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-[0.12em] transition-colors {{ $hasActiveChild ? 'text-[#E8820C]' : 'text-slate-400 hover:text-slate-600' }}"
                            data-sidebar-dropdown-toggle
                            aria-expanded="true"
                        >
                            <span>{{ $navItem['label'] }}</span>
                            <svg
                                class="h-3.5 w-3.5 transition-transform duration-200"
                                data-sidebar-dropdown-arrow
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Dropdown children (open by default) --}}
                        <div class="mt-1 space-y-0.5 pl-1" data-sidebar-dropdown-menu>
                            @foreach ($navItem['children'] as $child)
                                @php
                                    $isChildActive = request()->routeIs(...$child['match']);
                                    $childRouteExists = \Illuminate\Support\Facades\Route::has($child['route']);
                                    $childHref = $childRouteExists ? route($child['route']) : '#';
                                    $childIconMarkup = $renderSidebarIcon($child['icon']);
                                @endphp

                                <a
                                    href="{{ $childHref }}"
                                    @unless($childRouteExists) aria-disabled="true" @endunless
                                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all {{ $isChildActive ? 'bg-[#E8820C] text-white shadow-[0_10px_30px_-18px_rgba(232,130,12,0.95)]' : 'text-slate-500 hover:bg-[#FFF5EA] hover:text-[#E8820C]' }}"
                                >
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $isChildActive ? 'bg-white/15' : 'bg-slate-50 text-slate-400 group-hover:bg-white group-hover:text-[#E8820C]' }}">
                                        {!! $childIconMarkup !!}
                                    </span>
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- User Profile & Logout --}}
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

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-white hover:text-slate-600"
                    aria-label="Logout"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <path d="M16 17l5-5-5-5"></path>
                        <path d="M21 12H9"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Sidebar dropdown toggle script --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-sidebar-dropdown-toggle]').forEach(toggle => {
            const dropdown = toggle.closest('[data-sidebar-dropdown]');
            const menu = dropdown.querySelector('[data-sidebar-dropdown-menu]');
            const arrow = toggle.querySelector('[data-sidebar-dropdown-arrow]');

            toggle.addEventListener('click', () => {
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                if (isExpanded) {
                    // Collapse
                    toggle.setAttribute('aria-expanded', 'false');
                    menu.style.maxHeight = menu.scrollHeight + 'px';
                    requestAnimationFrame(() => {
                        menu.style.overflow = 'hidden';
                        menu.style.maxHeight = '0';
                        menu.style.opacity = '0';
                        menu.style.marginTop = '0';
                    });
                    arrow.style.transform = 'rotate(-90deg)';
                } else {
                    // Expand
                    toggle.setAttribute('aria-expanded', 'true');
                    menu.style.overflow = 'hidden';
                    menu.style.maxHeight = menu.scrollHeight + 'px';
                    menu.style.opacity = '1';
                    menu.style.marginTop = '0.25rem';
                    arrow.style.transform = 'rotate(0deg)';

                    // Clean up after transition
                    menu.addEventListener('transitionend', () => {
                        menu.style.maxHeight = '';
                        menu.style.overflow = '';
                    }, { once: true });
                }
            });

            // Set transition styles
            menu.style.transition = 'max-height 0.2s ease, opacity 0.2s ease, margin-top 0.15s ease';
            arrow.style.transition = 'transform 0.2s ease';
        });
    });
</script>
