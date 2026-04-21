{{-- Client Navbar --}}
<nav id="client-navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            {{-- Logo --}}
            <a href="{{ route('client.home') }}" class="flex items-center gap-2 group">
                <div class="w-10 h-10 flex items-center justify-center">
                    <img src="{{ asset('images/logo/logo-halositek.png') }}" alt="HaloSitek Logo" class="w-10 h-10 object-contain">
                </div>
                <span class="text-2xl font-bold text-slate-900">
                    Halo<span class="text-[#E8820C]">Sitek</span>
                </span>
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('client.home') }}"
                   class="nav-link text-sm font-medium transition-colors duration-200 {{ request()->routeIs('client.home') ? 'text-[#E8820C]' : 'text-slate-600 hover:text-[#E8820C]' }}">
                    Home
                </a>
                <a href="{{ route('client.download') }}"
                   class="nav-link text-sm font-medium transition-colors duration-200 {{ request()->routeIs('client.download') ? 'text-[#E8820C]' : 'text-slate-600 hover:text-[#E8820C]' }}">
                    Download
                </a>
                <a href="{{ route('client.about') }}"
                   class="nav-link text-sm font-medium transition-colors duration-200 {{ request()->routeIs('client.about') ? 'text-[#E8820C]' : 'text-slate-600 hover:text-[#E8820C]' }}">
                    About
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <button id="mobile-menu-btn" class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <div class="flex flex-col gap-2 bg-white/95 backdrop-blur-md rounded-xl p-4 shadow-lg border border-slate-100">
                <a href="{{ route('client.home') }}"
                   class="px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('client.home') ? 'bg-[#E8820C]/10 text-[#E8820C]' : 'text-slate-600 hover:bg-slate-50' }}">
                    Home
                </a>
                <a href="{{ route('client.download') }}"
                   class="px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('client.download') ? 'bg-[#E8820C]/10 text-[#E8820C]' : 'text-slate-600 hover:bg-slate-50' }}">
                    Download
                </a>
                <a href="{{ route('client.about') }}"
                   class="px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('client.about') ? 'bg-[#E8820C]/10 text-[#E8820C]' : 'text-slate-600 hover:bg-slate-50' }}">
                    About
                </a>
            </div>
        </div>
    </div>
</nav>
