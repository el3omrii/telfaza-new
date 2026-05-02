@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div class="auth-card">
    <div class="auth-card-title">Welcome back</div>
    <div class="auth-card-subtitle">Sign in to your StreamPanel account</div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('email') && !$errors->has('password'))
        <div class="alert alert-error">{{ $errors->first('email') }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST" novalidate>
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-error' : '' }}"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    autocomplete="email"
                    autofocus
                    required>
            </div>
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <span class="input-icon">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control {{ $errors->has('password') ? 'is-error' : '' }}"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="form-row">
            <label class="remember-label">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
        </div>

        <button type="submit" class="btn-submit">Sign In</button>
    </form>
</div>
@endsection