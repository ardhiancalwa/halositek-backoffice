@extends('client.layout.app')

@section('title', 'Download - HaloSitek Mobile App')
@section('meta_description', 'Download HaloSitek Mobile - Architecture in your pocket. Design, consult, and manage your projects from anywhere in the world.')

@section('content')
{{-- ============================================ --}}
{{-- HERO SECTION --}}
{{-- ============================================ --}}
<section class="relative min-h-screen flex items-center overflow-hidden hero-gradient">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 pt-28 pb-20 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Left Content --}}
            <div class="order-2 lg:order-1">
                <div class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-full text-xs font-medium mb-6">
                    <span class="w-2 h-2 bg-[#E8820C] rounded-full animate-pulse"></span>
                    Now Available on iOS & Android
                </div>

                <h1 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 leading-[1.1] mb-6">
                    Architecture<br>
                    <span class="text-[#E8820C] italic">in your pocket.</span>
                </h1>

                <p class="text-base text-slate-500 leading-relaxed mb-8 max-w-md">
                    HaloSitek brings professional architectural precision to your mobile device. Design, consult, and manage your projects from anywhere in the world.
                </p>

                <a href="#"
                   class="inline-flex items-center gap-3 bg-slate-900 text-white px-6 py-3.5 rounded-lg text-sm font-semibold hover:bg-slate-800 transition-all duration-300 group shadow-lg shadow-slate-900/20 mb-8">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Here !
                </a>

                {{-- User Avatars --}}
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <img src="{{ asset('images/images/img-asset18.png') }}" alt="AI Consultation" class="w-10 h-10 rounded-full bg-slate-300 shrink-0">
                        <img src="{{ asset('images/images/img-asset19.png') }}" alt="AI Consultation" class="w-10 h-10 rounded-full bg-slate-300 shrink-0">
                        <img src="{{ asset('images/images/img-asset20.png') }}" alt="AI Consultation" class="w-10 h-10 rounded-full bg-slate-300 shrink-0">
                    </div>
                    <p class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-700">25k+</span> active architects using HaloSitek Mobile
                    </p>
                </div>
            </div>

            {{-- Right Content - Phone Mockup --}}
            <div class="order-1 lg:order-2 flex justify-center">
                <div class="relative">
                    {{-- Phone Frame --}}
                    <div class="relative w-64 md:w-72 h-[520px] md:h-[580px] bg-white rounded-[3rem] border-[6px] border-slate-900 shadow-2xl shadow-slate-300/50 overflow-hidden">
                        {{-- Phone Screen Placeholder --}}
                        <img src="{{ asset('images/images/img-asset21.png') }}" alt="AI Consultation" class="w-full h-full object-cover object-top">
                    </div>
                    {{-- Secondary Phone (behind) --}}
                    <div class="absolute -right-24 top-8 w-52 md:w-60 h-[440px] md:h-[490px] bg-white rounded-[2.5rem] border-[5px] border-slate-600/70 shadow-xl shadow-slate-200/50 overflow-hidden -z-10 rotate-12">
                        <img src="{{ asset('images/images/img-asset22.png') }}" alt="AI Consultation" class="w-full h-full object-cover object-top">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- MOBILE-FIRST EXCELLENCE --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal-on-scroll">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-slate-900 mb-4">
                <span class="text-[#E8820C]">Mobile-First</span> Excellence
            </h2>
            <p class="text-slate-500 max-w-xl mx-auto">
                Designed to perform in the field. HaloSitek Mobile bridges the gap between digital vision and physical site reality.
            </p>
        </div>

        {{-- Feature Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- AI Consultation Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-8 hover:shadow-xl hover:shadow-slate-100 transition-all duration-500 reveal-on-scroll">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1">
                        <div class="w-10 h-10 rounded-xl bg-[#E8820C]/10 flex items-center justify-center mb-5">
                            <svg class="w-5 h-5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">AI Consultation</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-5">
                            Our proprietary AI analyzes your site measurements and local zoning laws instantly. Get preliminary structural advice and design recommendations powered by a neural network trained on millions of architectural blueprints.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E8820C]"></span>
                                Zoning Analysis
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#E8820C]"></span>
                                Structural Feasibility
                            </span>
                        </div>
                    </div>
                    <img src="{{ asset('images/images/img-asset10.png') }}" alt="AI Consultation" class="w-full md:w-48 shrink-0 h-40 md:h-auto rounded-xl object-cover">
                </div>
            </div>

            {{-- Live Chat Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-8 hover:shadow-xl hover:shadow-slate-100 transition-all duration-500 reveal-on-scroll reveal-delay-1">
                <div class="w-10 h-10 rounded-xl bg-[#E8820C]/10 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-3">Live Chat with Architects</h3>
                <p class="text-sm text-slate-500 leading-relaxed mb-6">
                    Real humans, real expertise. Connect with licensed architects in under 5 minutes for real-time site inspections and feedback.
                </p>
                {{-- Architect Online Status --}}
                <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-4">
                    <img src="{{ asset('images/images/img-asset17.png') }}" alt="AI Consultation" class="w-10 h-10 rounded-full bg-slate-300 shrink-0">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Architect Elena</p>
                        <p class="text-xs font-medium text-emerald-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online Now
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Design Preview Card (Full Width) --}}
        <div class="mt-6 reveal-on-scroll">
            <div class="relative bg-slate-900 rounded-2xl overflow-hidden">
                <img src="{{ asset('images/images/img-asset11.png') }}" alt="Design Preview" class="absolute inset-0 opacity-30 w-full h-full object-cover">
                <div class="relative z-10 p-8 md:p-12">
                    <div class="max-w-md">
                        <div class="w-10 h-10 rounded-xl bg-[#E8820C]/20 flex items-center justify-center mb-5">
                            <svg class="w-5 h-5 text-[#E8820C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Design Preview</h3>
                        <p class="text-sm text-white/70 leading-relaxed">
                            Project your designs onto real-world landscapes using augmented reality. See how your vision fits the actual terrain before breaking ground.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
