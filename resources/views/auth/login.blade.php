@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div class="bg-surface border border-border rounded-[10px] shadow-2xl overflow-hidden">
    {{-- Accent bar --}}
    <div class="h-0.5 bg-gradient-to-r from-accent via-yellow-400 to-transparent"></div>

    <div class="px-8 py-9">
        <h1 class="font-display font-bold text-xl mb-1">Welcome back</h1>
        <p class="text-muted text-sm mb-7">Sign in to your StreamPanel account</p>

        @if(session('success'))
            <div class="mb-5 px-4 py-3 rounded-[10px] text-sm bg-green-500/10 border border-green-500/30 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('email'))
            <div class="mb-5 px-4 py-3 rounded-[10px] text-sm bg-red-500/10 border border-red-500/30 text-red-400">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-[0.75rem] font-medium uppercase tracking-wider text-muted mb-1.5" for="email">
                    Email address
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email" autofocus required
                           class="w-full pl-10 pr-3.5 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 placeholder-muted transition-colors focus:outline-none focus:ring-0
                                  {{ $errors->has('email') ? 'border-red-500' : 'border-border focus:border-accent' }}">
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label class="block text-[0.75rem] font-medium uppercase tracking-wider text-muted mb-1.5" for="password">
                    Password
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password" required
                           class="w-full pl-10 pr-3.5 py-2.5 bg-bg border rounded-[10px] text-sm text-gray-200 placeholder-muted transition-colors focus:outline-none focus:ring-0
                                  {{ $errors->has('password') ? 'border-red-500' : 'border-border focus:border-accent' }}">
                </div>
            </div>

            {{-- Remember me --}}
            <div class="flex items-center mb-6">
                <label class="flex items-center gap-2 text-sm text-muted cursor-pointer select-none">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-accent bg-bg border-border">
                    Remember me
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-accent hover:bg-yellow-400 text-black font-display font-bold text-sm rounded-[10px] transition-colors active:scale-[.99]">
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection
