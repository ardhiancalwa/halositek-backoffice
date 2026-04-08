@extends('layouts.auth')

@section('title', 'Login - HaloSitek')

@section('content')
<div>
    <h1 class="text-2xl font-bold text-slate-900 mb-1">Welcome Back</h1>
    <p class="text-slate-500 text-sm mb-8">Please enter your details to access your dashboard</p>

    <div
        data-auth-alert
        class="mb-4 hidden rounded-lg border px-3 py-3 text-sm"
        role="alert"
        aria-live="polite"
    ></div>

    <form
        method="POST"
        action="{{ url('/api/v1/login') }}"
        data-auth-form
        data-auth-mode="login"
        data-auth-endpoint="{{ url('/api/v1/login') }}"
        data-auth-redirect="{{ route('dashboard') }}"
    >

        <div class="mb-5">
            <label for="email" class="block text-sm font-semibold text-slate-900 mb-2">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition"
                required
                autofocus
            >
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-slate-900 mb-2">Password</label>
            <div class="relative">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition pr-12"
                    required
                >
                <button type="button" data-toggle-password="password" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="mb-6">
            <a href="#" class="text-sm text-[#E8820C] hover:text-[#d4750a] font-medium">Forgot Password?</a>
        </div>

        <button
            type="submit"
            data-auth-submit
            class="w-full bg-[#E8820C] hover:bg-[#d4750a] text-white font-bold py-3 px-4 rounded-lg text-sm uppercase tracking-wider transition"
        >
            Login
        </button>

        <p class="text-center text-sm text-slate-500 mt-6">
            Don't have any account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-semibold">Register now</a>
        </p>
    </form>
</div>
@endsection
