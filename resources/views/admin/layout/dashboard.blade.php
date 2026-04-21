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
<body class="bg-gray-50 font-sans antialiased text-slate-900" data-protected-shell>
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
                    <div class="relative w-80">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-[#E8820C]" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#E8820C]/20 focus:border-[#E8820C] sm:text-sm transition-all" placeholder="Search data">
                    </div>
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
