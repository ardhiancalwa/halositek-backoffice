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
