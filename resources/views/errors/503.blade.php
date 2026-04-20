<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Under Maintenance | HaloSitek</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-playfair { font-family: 'Playfair Display', serif; }

        .error-glow {
            text-shadow: 0 0 80px rgba(232, 130, 12, 0.3), 0 0 160px rgba(232, 130, 12, 0.1);
        }

        .crane-swing {
            transform-origin: 130px 30px;
            animation: craneSwing 4s ease-in-out infinite;
        }

        @keyframes craneSwing {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        .gear-spin {
            transform-origin: center;
            animation: gearSpin 4s linear infinite;
        }

        @keyframes gearSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .gear-spin-reverse {
            transform-origin: center;
            animation: gearSpinReverse 3s linear infinite;
        }

        @keyframes gearSpinReverse {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        .dots-loading::after {
            content: '';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
        }

        .progress-bar {
            background: linear-gradient(90deg, #E8820C, #f59e0b, #E8820C);
            background-size: 200% 100%;
            animation: progressShimmer 2s ease-in-out infinite;
        }

        @keyframes progressShimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
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
            {{-- Decorative SVG - Construction Scene --}}
            <div class="relative mb-2 flex justify-center">
                <svg class="w-48 h-48 md:w-64 md:h-64" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    {{-- Blueprint grid --}}
                    <line x1="20" y1="40" x2="180" y2="40" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="80" x2="180" y2="80" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="120" x2="180" y2="120" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="20" y1="160" x2="180" y2="160" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="40" y1="20" x2="40" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="80" y1="20" x2="80" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="120" y1="20" x2="120" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    <line x1="160" y1="20" x2="160" y2="180" stroke="#e2e8f0" stroke-width="0.5"/>
                    {{-- Building under construction --}}
                    <path d="M55 160 L55 90 L100 60 L145 90 L145 160" stroke="#94a3b8" stroke-width="2" fill="none" stroke-linecap="round" stroke-dasharray="6 3"/>
                    <rect x="70" y="105" width="15" height="20" stroke="#cbd5e1" stroke-width="1.5" fill="none" rx="1" stroke-dasharray="4 2"/>
                    <rect x="115" y="105" width="15" height="20" stroke="#cbd5e1" stroke-width="1.5" fill="none" rx="1" stroke-dasharray="4 2"/>
                    <rect x="90" y="130" width="20" height="30" stroke="#cbd5e1" stroke-width="1.5" fill="none" rx="1" stroke-dasharray="4 2"/>
                    {{-- Crane --}}
                    <g class="crane-swing">
                        <line x1="130" y1="30" x2="130" y2="160" stroke="#E8820C" stroke-width="2" stroke-linecap="round"/>
                        <line x1="130" y1="30" x2="170" y2="30" stroke="#E8820C" stroke-width="2" stroke-linecap="round"/>
                        <line x1="130" y1="30" x2="115" y2="30" stroke="#E8820C" stroke-width="2" stroke-linecap="round"/>
                        <line x1="170" y1="30" x2="170" y2="55" stroke="#E8820C" stroke-width="1" stroke-linecap="round"/>
                        <rect x="164" y="55" width="12" height="10" stroke="#E8820C" stroke-width="1.5" fill="none" rx="1"/>
                        {{-- Support cables --}}
                        <line x1="130" y1="30" x2="150" y2="30" stroke="#E8820C" stroke-width="0.8"/>
                        <line x1="130" y1="30" x2="130" y2="50" stroke="#E8820C" stroke-width="0.8"/>
                    </g>
                    {{-- Gears --}}
                    <g class="gear-spin" style="transform-origin: 42px 155px;">
                        <circle cx="42" cy="155" r="10" stroke="#E8820C" stroke-width="1.5" fill="none"/>
                        <line x1="42" y1="143" x2="42" y2="147" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="42" y1="163" x2="42" y2="167" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="30" y1="155" x2="34" y2="155" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="50" y1="155" x2="54" y2="155" stroke="#E8820C" stroke-width="1.5" stroke-linecap="round"/>
                    </g>
                    <g class="gear-spin-reverse" style="transform-origin: 30px 145px;">
                        <circle cx="30" cy="145" r="6" stroke="#E8820C" stroke-width="1" fill="none"/>
                    </g>
                    {{-- Ground line --}}
                    <line x1="20" y1="160" x2="180" y2="160" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>

            {{-- Error Code --}}
            <h1 class="font-playfair text-8xl md:text-9xl font-bold text-[#E8820C] error-glow fade-in">
                503
            </h1>

            {{-- Message --}}
            <h2 class="font-playfair text-2xl md:text-3xl font-bold text-slate-900 mt-4 mb-4 fade-in fade-in-delay-1">
                Under Construction
            </h2>
            <p class="text-slate-500 leading-relaxed max-w-md mx-auto mb-4 fade-in fade-in-delay-2">
                Our architects are performing scheduled maintenance to reinforce the structural integrity of the platform. We'll be back shortly.
            </p>

            {{-- Progress bar --}}
            <div class="max-w-xs mx-auto mb-10 fade-in fade-in-delay-2">
                <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full w-full progress-bar rounded-full"></div>
                </div>
                <p class="text-xs text-slate-400 mt-2 dots-loading">Rebuilding systems</p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in fade-in-delay-3">
                <button onclick="location.reload()"
                        class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-slate-800 transition-all duration-300 shadow-lg shadow-slate-900/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh Page
                </button>
                <a href="mailto:telkomuniversity.gmail.com"
                   class="inline-flex items-center gap-2 bg-white text-slate-700 px-6 py-3 rounded-lg text-sm font-semibold border border-slate-200 hover:border-[#E8820C] hover:text-[#E8820C] transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Contact Us
                </a>
            </div>
        </div>
    </main>

</body>
</html>
