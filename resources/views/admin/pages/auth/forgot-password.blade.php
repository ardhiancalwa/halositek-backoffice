@extends('admin.layout.auth')

@section('title', 'Forgot Password - HaloSitek')

@section('content')
<div>
    <h1 class="auth-title mb-1 text-2xl font-bold">Forgot Password</h1>
    <p class="auth-subtitle mb-8 text-sm">Enter your email address and we'll send you a link to reset your password</p>

    @if (session('status'))
        <div
            class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            role="alert"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div
            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-600"
            role="alert"
            aria-live="polite"
        >
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.auth.forgot-password.submit') }}">
        @csrf

        <div class="mb-6">
            <label for="email" class="auth-label mb-2 block text-sm font-semibold">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your registered email"
                class="auth-input w-full rounded-lg border px-4 py-3 text-sm transition"
                required
                autofocus
            >
        </div>

        <button
            type="submit"
            class="auth-primary-button mb-4 w-full cursor-pointer rounded-lg px-4 py-3 text-sm font-bold uppercase tracking-wider transition"
        >
            Send Reset Link
        </button>

        <div class="text-center">
            <a
                href="{{ route('admin.auth.login') }}"
                class="auth-accent-link inline-flex items-center gap-1.5 text-sm font-semibold transition"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Login
            </a>
        </div>
    </form>
</div>
@endsection
