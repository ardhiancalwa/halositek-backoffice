@extends('client.layout.app')

@section('title', 'HaloSitek - Redefining the Architectural Ledger')
@section('meta_description', 'Precision tracking meets visionary design. HaloSitek offers an unmatched digital archive for modern developers and luxury estate curators.')

@section('content')
{{-- ============================================ --}}
{{-- HERO SECTION --}}
{{-- ============================================ --}}
<section class="relative min-h-screen flex items-center overflow-hidden">
    {{-- Hero Background Image --}}
    <div class="absolute inset-0 z-0">
        <img src="{{ asset('images/images/img-asset12.png') }}" alt="Architectural Hero" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 pt-32 pb-20 w-full">
        <div class="max-w-2xl">
            <h1 class="font-playfair text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.1] mb-6">
                <span class="italic">Redefining the</span><br>
                <span class="text-[#E8820C]">Architectural<br>Ledger.</span>
            </h1>
            <p class="text-base md:text-lg text-white/80 leading-relaxed mb-8 max-w-lg">
                Precision tracking meets visionary design. HaloSitek offers an unmatched digital archive for modern developers and luxury estate curators.
            </p>
            <a href="{{ route('client.download') }}"
               class="inline-flex items-center gap-3 bg-white text-slate-900 px-6 py-3.5 rounded-lg text-sm font-semibold hover:bg-slate-100 transition-all duration-300 group shadow-lg shadow-black/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Here !
            </a>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- STATS BAR --}}
{{-- ============================================ --}}
<section class="bg-[#E8820C]">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="reveal-on-scroll">
                <p class="text-xs uppercase tracking-[0.15em] text-white/70 mb-1">Active Building</p>
                <p class="stat-number text-2xl md:text-3xl font-bold text-white">120.480+</p>
            </div>
            <div class="reveal-on-scroll reveal-delay-1">
                <p class="text-xs uppercase tracking-[0.15em] text-white/70 mb-1">Total Capital Revenue</p>
                <p class="stat-number text-2xl md:text-3xl font-bold text-white">Rp. 120.000.00</p>
            </div>
            <div class="reveal-on-scroll reveal-delay-2">
                <p class="text-xs uppercase tracking-[0.15em] text-white/70 mb-1">AI Suggestion</p>
                <p class="stat-number text-2xl md:text-3xl font-bold text-white">2400</p>
            </div>
            <div class="reveal-on-scroll reveal-delay-3">
                <p class="text-xs uppercase tracking-[0.15em] text-white/70 mb-1">Active Architect</p>
                <p class="stat-number text-2xl md:text-3xl font-bold text-white">1800</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CURATED DESIGN VAULT --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="mb-12 reveal-on-scroll">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-slate-900 mb-3">Curated Design Vault</h2>
            <p class="text-sm text-slate-500 max-w-xl">
                Explore our pages of architectural masterpieces, from avant-garde minimalism to time-honoured traditional frameworks.
            </p>
        </div>

        {{-- Bento Grid Gallery --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 auto-rows-[200px] md:auto-rows-[240px]">
            {{-- Item 1: Modernism Revived --}}
            <div class="relative col-span-1 row-span-1 rounded-2xl overflow-hidden group reveal-on-scroll">
                <img src="{{ asset('images/images/img-asset13.png') }}" alt="Modernism Revived" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <h3 class="text-white font-semibold text-sm">Modernism Revived</h3>
                    <p class="text-white/70 text-xs mt-0.5">Contemporary with glass-centric aesthetics</p>
                </div>
            </div>

            {{-- Item 2: Pure Minimalist --}}
            <div class="relative col-span-1 row-span-1 rounded-2xl overflow-hidden group reveal-on-scroll reveal-delay-1">
                <img src="{{ asset('images/images/img-asset14.png') }}" alt="Pure Minimalist" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <h3 class="text-white font-semibold text-sm">Pure Minimalist</h3>
                    <p class="text-white/70 text-xs mt-0.5">Where negative space tells the story</p>
                </div>
            </div>

            {{-- Item 3: Corporate Ledger (large) --}}
            <div class="relative col-span-2 row-span-2 rounded-2xl overflow-hidden group reveal-on-scroll reveal-delay-2">
                <img src="{{ asset('images/images/img-asset16.png') }}" alt="Corporate Ledger" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <h3 class="text-white font-semibold">Corporate Ledger</h3>
                    <p class="text-white/70 text-sm mt-1">Bridging commerce with architectural excellence and luxury design standards</p>
                </div>
            </div>

            {{-- Item 4: Timeless Tradition --}}
            <div class="relative col-span-2 row-span-1 rounded-2xl overflow-hidden group reveal-on-scroll reveal-delay-3">
                <img src="{{ asset('images/images/img-asset15.png') }}" alt="Timeless Tradition" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <h3 class="text-white font-semibold text-sm">Timeless Tradition</h3>
                    <p class="text-white/70 text-xs mt-0.5">Classical proportions meeting modern standards</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- OUR MISSION --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal-on-scroll">
            <p class="text-xs uppercase tracking-[0.2em] text-[#E8820C] font-semibold mb-3">Our Mission</p>
            <h2 class="font-playfair text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 mb-5">
                Architecting the future of property<br class="hidden md:block"> intelligence.
            </h2>
            <p class="text-slate-500 max-w-2xl mx-auto leading-relaxed">
                HaloSitek was founded on the principle that every architectural masterpiece deserves a perfect digital record. We bridge the gap between visionary design and rigorous documentation.
            </p>
        </div>

        <div class="mt-12">
            <p class="text-slate-500 max-w-3xl mx-auto text-center leading-relaxed mb-12 reveal-on-scroll">
                Our mission is to empower developers, architects, and luxury estate curators with a certifiably true ledger system of record that preserves the integrity of design intent while providing real-time operational insights.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 reveal-on-scroll">
                {{-- Visionary Card --}}
                <div class="bg-white rounded-2xl p-8 border border-slate-100 hover:shadow-lg hover:shadow-slate-100 transition-all duration-300">
                    <div class="w-10 h-10 rounded-lg bg-[#E8820C]/10 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xs uppercase tracking-[0.15em] text-[#E8820C] font-semibold mb-2">Visionary</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Every masterpiece ever, every ruin, every significance—archived in the next generation.
                    </p>
                </div>

                {{-- Precise Card --}}
                <div class="bg-white rounded-2xl p-8 border border-slate-100 hover:shadow-lg hover:shadow-slate-100 transition-all duration-300">
                    <div class="w-10 h-10 rounded-lg bg-[#E8820C]/10 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xs uppercase tracking-[0.15em] text-[#E8820C] font-semibold mb-2">Precise</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Unmatched accuracy, every entry, every verification—your trust in every design detail.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CTA / DOWNLOAD BANNER --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-slate-900 relative overflow-hidden">
    {{-- Decorative elements --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#E8820C]/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#E8820C]/5 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <div class="reveal-on-scroll">
            <h2 class="font-playfair text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Take the ledger<br>
                <span class="text-[#E8820C] italic">wherever you go.</span>
            </h2>
            <p class="text-slate-400 max-w-lg mx-auto mb-8 leading-relaxed">
                Access your full architectural portfolio, real-time capital tracking, and secure design versions directly from your mobile device.
            </p>
            <a href="{{ route('client.download') }}"
               class="inline-flex items-center gap-3 bg-white text-slate-900 px-7 py-3.5 rounded-lg text-sm font-semibold hover:bg-slate-100 transition-all duration-300 shadow-lg shadow-black/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Here !
            </a>
        </div>
    </div>
</section>
@endsection
