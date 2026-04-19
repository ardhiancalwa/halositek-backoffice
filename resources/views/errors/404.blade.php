<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | HaloSitek</title>
    <link rel="icon" href="{{ asset('images/logo/logo-halositek.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }

        .error-glow {
            text-shadow: 0 0 80px rgba(232, 130, 12, 0.3), 0 0 160px rgba(232, 130, 12, 0.1);
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .line-draw {
            stroke-dasharray: 800;
            stroke-dashoffset: 800;
            animation: draw 2s ease-out forwards;
        }

        @keyframes draw {
            to { stroke-dashoffset: 0; }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .fade-in-delay-1 { animation-delay: 0.3s; opacity: 0; }
        .fade-in-delay-2 { animation-delay: 0.5s; opacity: 0; }
        .fade-in-delay-3 { animation-delay: 0.7s; opacity: 0; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-white font-sans antialiased text-slate-900 min-h-screen flex flex-col">

    {{-- Content --}}
    <main class="flex-1 flex items-center justify-center px-6">
        <div class="max-w-2xl mx-auto text-center py-20">
            {{-- Decorative SVG --}}
            <div class="relative mb-4 flex justify-center">
                <svg class="float-animation w-48 h-48 md:w-64 md:h-64" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    {{-- Blueprint grid lines --}}
                    <line x1="20" y1="40" x2="180" y2="40" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="20" y1="80" x2="180" y2="80" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="20" y1="120" x2="180" y2="120" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="20" y1="160" x2="180" y2="160" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="40" y1="20" x2="40" y2="180" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="80" y1="20" x2="80" y2="180" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="120" y1="20" x2="120" y2="180" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    <line x1="160" y1="20" x2="160" y2="180" stroke="#e2e8f0" stroke-width="0.5" class="line-draw"/>
                    {{-- Building outline (incomplete/broken) --}}
                    <path d="M60 160 L60 70 L100 50 L140 70 L140 160" stroke="#E8820C" stroke-width="2" fill="none" stroke-linecap="round" class="line-draw"/>
                    <line x1="80" y1="100" x2="80" y2="130" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round" class="line-draw"/>
                    <line x1="80" y1="100" x2="100" y2="100" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round" class="line-draw"/>
                    <rect x="90" y="120" width="20" height="40" stroke="#E8820C" stroke-width="1.5" fill="none" rx="1" class="line-draw"/>
                    {{-- Question mark --}}
                    <text x="100" y="105" text-anchor="middle" fill="#E8820C" font-size="24" font-weight="bold" opacity="0.4">?</text>
                    {{-- Dashed broken path --}}
                    <path d="M30 160 L50 160" stroke="#cbd5e1" stroke-width="1.5" stroke-dasharray="4 3" class="line-draw"/>
                    <path d="M150 160 L170 160" stroke="#cbd5e1" stroke-width="1.5" stroke-dasharray="4 3" class="line-draw"/>
                </svg>
            </div>

            {{-- Error Code --}}
            <h1 class="font-playfair text-8xl md:text-9xl font-bold text-[#E8820C] error-glow fade-in">
                404
            </h1>

            {{-- Message --}}
            <h2 class="font-playfair text-2xl md:text-3xl font-bold text-slate-900 mt-4 mb-4 fade-in fade-in-delay-1">
                Page Not Found
            </h2>
            <p class="text-slate-500 leading-relaxed max-w-md mx-auto mb-10 fade-in fade-in-delay-2">
                The page you are looking for does not exist or has been moved.
            </p>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in fade-in-delay-3">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-slate-800 transition-all duration-300 shadow-lg shadow-slate-900/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Back to Home
                </a>
                <button onclick="history.back()"
                        class="inline-flex items-center gap-2 bg-white text-slate-700 px-6 py-3 rounded-lg text-sm font-semibold border border-slate-200 hover:border-[#E8820C] hover:text-[#E8820C] transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Go Back
                </button>
            </div>
        </div>
    </main>

</body>
</html>
