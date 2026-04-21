@extends('client.layout.app')

@section('title', 'About - HaloSitek')
@section('meta_description', 'HaloSitek exists at the intersection of structural integrity and digital fluidity. We build systems that don\'t just function—they endure.')

@section('content')
{{-- ============================================ --}}
{{-- HERO SECTION --}}
{{-- ============================================ --}}
<section class="relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 pt-28 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left Content --}}
            <div class="pt-4">
                <p class="text-xs uppercase tracking-[0.2em] text-[#E8820C] font-semibold mb-4">Our Vision & Story</p>
                <h1 class="font-playfair text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 leading-[1.1] mb-6">
                    Architecting the<br>Future.
                </h1>
                <p class="text-base text-slate-500 leading-relaxed max-w-md">
                    HaloSitek exists at the intersection of structural integrity and digital fluidity. We build systems that don't just function—they endure.
                </p>
            </div>

            {{-- Right Content - Image + Badge --}}
            <div class="relative">
                <img src="{{ asset('images/images/img-asset1.png') }}" alt="About HaloSitek" class="w-full h-full object-cover rounded-2xl">
                {{-- Badge --}}
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 lg:left-auto lg:translate-x-0 lg:right-8 bg-[#E8820C] text-white px-6 py-4 rounded-xl shadow-lg shadow-[#E8820C]/30">
                    <p class="text-3xl font-bold">12+</p>
                    <p class="text-xs uppercase tracking-widest font-medium text-white/80">Years of Precision</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- OUR MISSION --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-[#FDFAF5] ">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            {{-- Left Content --}}
            <div class="reveal-on-scroll">
                <h2 class="font-playfair text-3xl md:text-4xl font-bold text-slate-900 mb-8">Our Mission</h2>

                <div class="space-y-8">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-1 h-5 bg-[#E8820C] rounded-full"></div>
                            <h3 class="font-bold text-slate-900">Immutable Integrity</h3>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed pl-4">
                            We believe that digital architecture should be as resilient as physical steel. Our frameworks are built to withstand the erosion of time and technology cycles.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-1 h-5 bg-[#E8820C] rounded-full"></div>
                            <h3 class="font-bold text-slate-900">Human-Centric Precision</h3>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed pl-4">
                            Design is not just aesthetic, it is the bridge between human intent and machine execution. We perfect every millimeter of that intersection.
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-1 h-5 bg-[#E8820C] rounded-full"></div>
                            <h3 class="font-bold text-slate-900">Sustainable Evolution</h3>
                        </div>
                        <p class="text-sm text-slate-500 leading-relaxed pl-4">
                            Building for today means anticipating tomorrow. Our modular approach ensures that growth doesn't require demolition.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Content - Image Collage --}}
            <div class="reveal-on-scroll reveal-delay-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <img src="{{ asset('images/images/img-asset2.png') }}" alt="Mission" class="w-full h-56 object-cover rounded-xl">
                        <img src="{{ asset('images/images/img-asset4.png') }}" alt="Mission" class="w-full h-32 object-cover rounded-xl">
                    </div>
                    <div class="space-y-4 pt-8">
                        <img src="{{ asset('images/images/img-asset3.png') }}" alt="Mission" class="w-full h-32 object-cover rounded-xl">
                        <img src="{{ asset('images/images/img-asset5.png') }}" alt="Mission" class="w-full h-56 object-cover rounded-xl">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- THE ARCHITECTS --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="text-center mb-16 reveal-on-scroll">
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-slate-900 mb-3">The Architects</h2>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400 font-medium">Crafting the Digital Blueprint</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 reveal-on-scroll">
            {{-- Left: Large Portrait (spans 2 rows) --}}
            <div class="relative overflow-hidden group md:row-span-2 aspect-[3/4]">
                <img src="{{ asset('images/images/img-asset6.png') }}" alt="Team Lead" class="w-full h-full object-cover rounded-2xl">
            </div>

            {{-- Right Top: Two small portraits side by side --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Elena Rossi --}}
                <div class="relative rounded-2xl overflow-hidden group aspect-square">
                    <img src="{{ asset('images/images/img-asset7.png') }}" alt="Elena Rossi" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <h3 class="text-white font-bold text-sm">Elena Rossi</h3>
                        <p class="text-[#E8820C] text-xs font-medium uppercase tracking-wider">Design Lead</p>
                    </div>
                </div>

                {{-- Marcus Chen --}}
                <div class="relative rounded-2xl overflow-hidden group aspect-square">
                    <img src="{{ asset('images/images/img-asset8.png') }}" alt="Marcus Chen" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-4">
                        <h3 class="text-white font-bold text-sm">Marcus Chen</h3>
                        <p class="text-[#E8820C] text-xs font-medium uppercase tracking-wider">Technical Director</p>
                    </div>
                </div>
            </div>

            {{-- Right Bottom: Wide team image --}}
            <div class="relative rounded-2xl overflow-hidden group aspect-[2/1]">
                <img src="{{ asset('images/images/img-asset9.png') }}" alt="Core Creative Studio" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-5">
                    <h3 class="text-white font-bold">Core Creative Studio</h3>
                    <p class="text-[#E8820C] text-xs font-medium uppercase tracking-wider">Multidisciplinary Team</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CTA SECTION --}}
{{-- ============================================ --}}
<section class="py-20 lg:py-28 bg-[#E8820C] relative overflow-hidden">
    {{-- Decorative --}}
    <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full blur-3xl -translate-y-1/2 -translate-x-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl translate-y-1/2 translate-x-1/2"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 text-center">
        <div class="reveal-on-scroll">
            <h2 class="font-playfair text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-8">
                Want to see all ?
            </h2>
            <a href="{{ route('client.download') }}"
               class="inline-flex items-center gap-3 bg-white text-[#E8820C] px-7 py-3.5 rounded-lg text-sm font-bold hover:bg-slate-50 transition-all duration-300 shadow-lg shadow-black/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Here !
            </a>
        </div>
    </div>
</section>
@endsection
