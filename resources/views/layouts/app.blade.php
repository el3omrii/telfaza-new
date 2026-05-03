<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    @php use Illuminate\Support\Facades\Auth; @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'StreamPanel') — StreamPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-bg text-gray-200 font-sans flex">

{{-- ── Sidebar ── --}}
<aside class="w-60 min-h-screen bg-surface border-r border-border flex flex-col fixed top-0 left-0 bottom-0 z-20">
    <div class="px-5 py-6 border-b border-border font-display font-extrabold text-xl tracking-tight">
        Stream<span class="text-accent">Panel</span>
    </div>

    <nav class="flex-1 px-2.5 py-4 space-y-0.5">
        <p class="text-[0.68rem] uppercase tracking-widest text-muted px-2.5 pt-3 pb-1.5">Content</p>

        <a href="{{ route('channels.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('channels.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
            </svg>
            Channels
        </a>

        <a href="{{ route('sources.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('sources.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14M15.54 8.46a5 5 0 010 7.07M8.46 8.46a5 5 0 000 7.07"/>
            </svg>
            Sources
        </a>

        <a href="{{ route('categories.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('categories.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Categories
        </a>

        <a href="{{ route('tags.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('tags.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><circle cx="7" cy="7" r="1"/>
            </svg>
            Tags
        </a>

        <a href="{{ route('countries.index') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('countries.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
            </svg>
            Countries
        </a>

        <p class="text-[0.68rem] uppercase tracking-widest text-muted px-2.5 pt-4 pb-1.5">Settings</p>

        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-[10px] text-sm font-medium transition-colors
                  {{ request()->routeIs('profile.*') ? 'bg-accent/10 text-accent' : 'text-muted hover:bg-accent/10 hover:text-accent' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profile
        </a>
    </nav>

    {{-- User block --}}
    <div class="flex items-center gap-2.5 px-4 py-3.5 border-t border-border shrink-0">
        <a href="{{ route('profile.edit') }}"
           class="w-8 h-8 rounded-full bg-gradient-to-br from-accent to-yellow-400 text-black font-display font-bold text-sm flex items-center justify-center shrink-0 hover:opacity-80 transition-opacity">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </a>
        <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 hover:opacity-80 transition-opacity">
            <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
            <p class="text-xs text-muted truncate">{{ Auth::user()->email }}</p>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
            @csrf
            <button type="submit" title="Sign out"
                    class="text-muted hover:text-red-400 hover:bg-red-400/10 p-1.5 rounded-lg transition-colors flex">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>
</aside>

{{-- ── Main ── --}}
<main class="ml-60 flex-1 min-h-screen p-8">
    @if(session('success'))
        <div class="mb-5 px-4 py-3 rounded-[10px] text-sm bg-green-500/10 border border-green-500/30 text-green-400">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 px-4 py-3 rounded-[10px] text-sm bg-red-500/10 border border-red-500/30 text-red-400">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>