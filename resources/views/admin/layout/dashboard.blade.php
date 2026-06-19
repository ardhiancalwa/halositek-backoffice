<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.plugins._top')

    <style>
        [data-protected-shell] {
            opacity: 0;
            visibility: hidden;
        }

        [data-protected-shell="ready"] {
            opacity: 1;
            visibility: visible;
        }
    </style>

    <script>
        (() => {
            const accessToken = window.localStorage.getItem('halositek.auth.access_token');

            if (!accessToken) {
                window.location.replace(@js(route('admin.auth.login')));
                return;
            }

            window.addEventListener('DOMContentLoaded', () => {
                document.body.setAttribute('data-protected-shell', 'ready');
            });
        })();
    </script>
</head>
<body class="bg-gray-50 font-sans antialiased text-slate-900">
    <div class="flex h-screen overflow-hidden">
        @include('admin.components._sidebar')

        <!-- Main Content Wrapper -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-[#fafafa]">
            <!-- Top Header -->
            <header class="w-full h-20 bg-white/50 backdrop-blur-sm px-8 flex items-center justify-between sticky top-0 z-10 shrink-0">
                @if(!request()->routeIs('admin.dashboard.index'))
                    <div class="w-full max-w-xl">
                        <a href="javascript:history.back()" class="text-slate-800 hover:text-slate-900 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                        </a>
                    </div>
                @endif

                <div class="flex items-center justify-end w-full gap-4">
                    <!-- Search Bar -->
                    @if(request()->routeIs(['admin.dashboard.users.index', 'admin.dashboard.designs.index', 'admin.dashboard.architects.index']))
                        <form method="GET" action="{{ url()->current() }}" class="relative w-full max-w-sm hidden md:block">
                            @foreach(request()->except(['search', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <input type="text" id="global-search-input" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full pl-10 pr-4 py-2 bg-slate-100 border border-transparent rounded-lg text-sm focus:outline-none focus:bg-white focus:border-[#E8820C] focus:ring-1 focus:ring-[#E8820C] transition-all">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </form>
                    @endif
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-8">
                @yield('content')
            </div>
            
            @include('admin.components._footer')
        </main>
    </div>
    
    @include('admin.plugins._bottom')
</body>
</html>
