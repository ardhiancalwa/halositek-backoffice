<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('admin.plugins._top')
</head>
<body class="h-screen bg-white font-sans antialiased overflow-hidden">
    <div class="flex h-screen">
        <div class="hidden lg:flex lg:w-1/2 relative">
            <div class="absolute inset-0 bg-slate-800">
                <img src="{{ asset('images/auth-bg.jpg') }}" alt="" class="h-full w-full object-cover opacity-80" onerror="this.style.display='none'">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/50 to-slate-900/30"></div>
            </div>

            <div class="relative z-10 flex flex-col justify-end p-12 text-white">
                <div class="flex items-center gap-2 mb-6">
                    <img src="{{ asset('images/vector.png') }}" alt="HaloSitek" class="h-8 w-auto">
                    <span class="text-[#E8820C] font-bold text-2xl">HaloSitek</span>
                </div>
                <h2 class="text-3xl font-bold leading-tight mb-3">
                    Elevating architectural<br>
                    vision through digital<br>
                    consultation.
                </h2>
                <p class="text-slate-300 text-sm leading-relaxed">
                    Join a community of elite architects and homeowners<br>
                    designing the future together.
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-10">
            <div class="auth-page-scale w-full max-w-md">
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('images/brand.png') }}" alt="HaloSitek" class="h-14">
                </div>

                @yield('content')
            </div>
        </div>
    </div>
    
    @include('admin.plugins._bottom')
</body>
</html>
