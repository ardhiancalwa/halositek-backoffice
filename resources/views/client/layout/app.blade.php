<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('client.plugins._top')

    <style>
        /* Navbar scroll state */
        .navbar-scrolled {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(80px);
            -webkit-backdrop-filter: blur(80px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Scroll reveal animations */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease-out, transform 0.7s ease-out;
        }

        .reveal-on-scroll.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-on-scroll.reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-on-scroll.reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-on-scroll.reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-on-scroll.reveal-delay-4 { transition-delay: 0.4s; }

        /* Playfair Display font-family utility */
        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* Custom gradient overlay for hero sections */
        .hero-gradient {
            background: linear-gradient(135deg, rgba(232, 130, 12, 0.08) 0%, rgba(255, 255, 255, 0) 50%);
        }

        /* Subtle pattern overlay */
        .pattern-dots {
            background-image: radial-gradient(circle, #e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Image placeholder styling */
        .img-placeholder {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .img-placeholder::after {
            content: '';
            width: 40px;
            height: 40px;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
        }

        /* Stat counter animation */
        .stat-number {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="bg-white font-sans antialiased text-slate-900">
    @include('client.components._navbar')

    <main>
        @yield('content')
    </main>

    @include('client.components._footer')

    @include('client.plugins._bottom')
</body>
</html>
