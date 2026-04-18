@extends('admin.layout.auth')

@section('title', 'Login - HaloSitek')

@section('content')
<div>
    <h1 class="auth-title mb-1 text-2xl font-bold">Welcome Back</h1>
    <p class="auth-subtitle mb-8 text-sm">Please enter your details to access your dashboard</p>

    @if ($errors->any())
        <div
            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-600"
            role="alert"
            aria-live="polite"
        >
            {{ $errors->first() }}
        </div>
    @elseif (session('status'))
        <div
            class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-3 text-sm text-green-700"
            role="alert"
            aria-live="polite"
        >
            {{ session('status') }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('login.submit') }}"
    >
        @csrf

        <div class="mb-5">
            <label for="email" class="auth-label mb-2 block text-sm font-semibold">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                class="auth-input w-full rounded-lg border px-4 py-3 text-sm transition"
                required
                autofocus
            >
        </div>

        <div class="mb-4">
            <label for="password" class="auth-label mb-2 block text-sm font-semibold">Password</label>
            <div class="relative">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="auth-input w-full rounded-lg border px-4 py-3 pr-12 text-sm transition"
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
            <a href="#" class="auth-primary-link text-sm font-medium">Forgot Password?</a>
        </div>

        <button
            type="submit"
            data-auth-submit
            class="auth-primary-button w-full rounded-lg px-4 py-3 text-sm font-bold uppercase tracking-wider transition"
        >
            Login
        </button>

    </form>
</div>
@endsection
