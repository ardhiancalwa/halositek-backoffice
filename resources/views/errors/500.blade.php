<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | HaloSitek</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }

        .error-glow {
            text-shadow: 0 0 80px rgba(239, 68, 68, 0.3), 0 0 160px rgba(239, 68, 68, 0.1);
        }

        .shake {
            animation: shake 0.6s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            15% { transform: translateX(-8px) rotate(-1deg); }
            30% { transform: translateX(6px) rotate(1deg); }
            45% { transform: translateX(-4px); }
            60% { transform: translateX(2px); }
        }

        .crack-line {
            stroke-dasharray: 200;
            stroke-dashoffset: 200;
            animation: crackDraw 1.2s ease-out 0.3s forwards;
        }

        @keyframes crackDraw {
            to { stroke-dashoffset: 0; }
        }

        .pulse-ring {
            animation: pulseRing 2s ease-out infinite;
        }

        @keyframes pulseRing {
            0% { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(1.5); opacity: 0; }
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
                <svg class="shake w-48 h-48 md:w-64 md:h-64" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    {{-- Blueprint grid --}}
                    <line x1="20" y1="40" x2="180" y2="40" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="80" x2="180" y2="80" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="120" x2="180" y2="120" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="160" x2="180" y2="160" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="40" y1="20" x2="40" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="80" y1="20" x2="80" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="120" y1="20" x2="120" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="160" y1="20" x2="160" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    {{-- Building with cracks --}}
                    <path d="M60 160 L60 65 L100 45 L140 65 L140 160" stroke="#94a3b8" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <rect x="75" y="85" width="20" height="25" stroke="#94a3b8" stroke-width="1.5" fill="none" rx="1"/>
                    <rect x="105" y="85" width="20" height="25" stroke="#94a3b8" stroke-width="1.5" fill="none" rx="1"/>
                    <rect x="90" y="125" width="20" height="35" stroke="#94a3b8" stroke-width="1.5" fill="none" rx="1"/>
                    {{-- Crack lines --}}
                    <path d="M100 45 L95 75 L105 90 L92 120" stroke="#ef4444" stroke-width="2" stroke-linecap="round" class="crack-line"/>
                    <path d="M95 75 L80 80" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" class="crack-line"/>
                    <path d="M105 90 L120 95" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" class="crack-line"/>
                    {{-- Warning circle --}}
                    <circle cx="100" cy="100" r="55" stroke="#ef4444" stroke-width="1" fill="none" opacity="0.2" class="pulse-ring"/>
                </svg>
            </div>

            {{-- Error Code --}}
            <h1 class="font-playfair text-8xl md:text-9xl font-bold text-red-500 error-glow fade-in">
                500
            </h1>

            {{-- Message --}}
            <h2 class="font-playfair text-2xl md:text-3xl font-bold text-slate-900 mt-4 mb-4 fade-in fade-in-delay-1">
                Structural Failure
            </h2>
            <p class="text-slate-500 leading-relaxed max-w-md mx-auto mb-10 fade-in fade-in-delay-2">
                Our systems experienced an unexpected structural failure. Our engineering team has been alerted and is working to restore the integrity of the ledger.
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
                <button onclick="location.reload()"
                        class="inline-flex items-center gap-2 bg-white text-slate-700 px-6 py-3 rounded-lg text-sm font-semibold border border-slate-200 hover:border-[#E8820C] hover:text-[#E8820C] transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Try Again
                </button>
            </div>
        </div>
    </main>

</body>
</html>
