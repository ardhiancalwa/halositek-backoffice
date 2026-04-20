{{-- Client Footer --}}
<footer class="bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            {{-- Brand Column --}}
            <div>
                <a href="{{ route('client.home') }}" class="flex items-center gap-2 mb-4">
                    <span class="text-lg font-bold text-slate-900">
                        Halo<span class="text-[#E8820C]">Sitek</span>
                    </span>
                </a>
                <p class="text-sm text-slate-500 leading-relaxed max-w-xs">
                    The definitive architectural ledger for modern developers and luxury estate curators. Redefining how the world tracks visionary design.
                </p>
            </div>

            {{-- Contact Column --}}
            <div>
                <h4 class="text-xs font-semibold tracking-[0.2em] uppercase text-slate-400 mb-5">Contact</h4>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-[#E8820C] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>telkomuniversity.gmail.com</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-[#E8820C] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>(022) 12348068</span>
                    </li>
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <svg class="w-4 h-4 text-[#E8820C] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        <span>@telkomuniversity</span>
                    </li>
                </ul>
            </div>

            {{-- Office Column --}}
            <div>
                <h4 class="text-xs font-semibold tracking-[0.2em] uppercase text-slate-400 mb-5">Office</h4>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Jl. Telekomunikasi No. 1, Bandung Terusan Buahbatu – Bojongsoang, Sukapura, Kec. Dayeuhkolot, Kabupaten Bandung, Jawa Barat 40257, Indonesia
                </p>
            </div>
        </div>
    </div>

    {{-- Bottom Bar --}}
    <div class="border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-400">
                &copy; {{ date('Y') }} HaloSitek Architectural Ledger. All Rights Reserved.
            </p>
            <div class="flex items-center gap-6">
                <a href="#" class="text-xs text-slate-400 hover:text-[#E8820C] transition-colors">Terms of Service</a>
                <a href="#" class="text-xs text-slate-400 hover:text-[#E8820C] transition-colors">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>
