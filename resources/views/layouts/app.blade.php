<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'StreamPanel') — StreamPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
        :root {
            --bg:       #0d0f14;
            --surface:  #161a23;
            --border:   #252b38;
            --accent:   #e8a020;
            --accent2:  #3b82f6;
            --text:     #e2e8f0;
            --muted:    #64748b;
            --danger:   #ef4444;
            --success:  #22c55e;
            --radius:   10px;
        }
 
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
 
        /* ── Sidebar ── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
        }
        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -.02em;
        }
        .sidebar-logo span { color: var(--accent); }
        .sidebar-nav { padding: 16px 10px; flex: 1; }
        .nav-section { font-size: .68rem; text-transform: uppercase; letter-spacing: .1em; color: var(--muted); padding: 12px 10px 6px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: var(--radius);
            color: var(--muted);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: all .15s;
            margin-bottom: 2px;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(232,160,32,.1);
            color: var(--accent);
        }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; }
 
        /* ── Main ── */
        .main { margin-left: 240px; flex: 1; padding: 32px; min-height: 100vh; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .page-title { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 700; }
        .page-subtitle { font-size: .875rem; color: var(--muted); margin-top: 2px; }
 
        /* ── Components ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--radius);
            font-size: .875rem; font-weight: 500; cursor: pointer;
            border: none; text-decoration: none; transition: all .15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-primary { background: var(--accent); color: #000; }
        .btn-primary:hover { background: #f5ad30; }
        .btn-secondary { background: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #2e3748; }
        .btn-danger { background: rgba(239,68,68,.15); color: var(--danger); border: 1px solid rgba(239,68,68,.3); }
        .btn-danger:hover { background: rgba(239,68,68,.25); }
        .btn-sm { padding: 5px 12px; font-size: .8rem; }
 
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-body { padding: 20px; }
 
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 11px 16px; text-align: left; font-size: .75rem; text-transform: uppercase; letter-spacing: .07em; color: var(--muted); border-bottom: 1px solid var(--border); }
        tbody td { padding: 12px 16px; font-size: .875rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: rgba(255,255,255,.02); }
 
        .badge {
            display: inline-block; padding: 2px 9px; border-radius: 999px;
            font-size: .72rem; font-weight: 600; letter-spacing: .04em;
        }
        .badge-amber  { background: rgba(232,160,32,.15); color: var(--accent); }
        .badge-blue   { background: rgba(59,130,246,.15); color: var(--accent2); }
        .badge-green  { background: rgba(34,197,94,.15);  color: var(--success); }
        .badge-gray   { background: var(--border); color: var(--muted); }
 
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: .8rem; font-weight: 500; margin-bottom: 6px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .form-control {
            width: 100%; padding: 10px 13px;
            background: var(--bg); border: 1px solid var(--border);
            border-radius: var(--radius); color: var(--text);
            font-family: 'DM Sans', sans-serif; font-size: .875rem;
            transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--accent); }
        select.form-control { cursor: pointer; }
 
        .alert { padding: 12px 16px; border-radius: var(--radius); font-size: .875rem; margin-bottom: 20px; }
        .alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: var(--success); }
        .alert-error   { background: rgba(239,68,68,.1);  border: 1px solid rgba(239,68,68,.3);  color: var(--danger); }
 
        .pagination { display: flex; gap: 6px; margin-top: 20px; }
        .pagination a, .pagination span {
            padding: 6px 12px; border-radius: 6px; font-size: .8rem;
            background: var(--surface); border: 1px solid var(--border); color: var(--text); text-decoration: none;
        }
        .pagination .active span { background: var(--accent); color: #000; border-color: var(--accent); }
 
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; }
        .stat-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 2rem; font-weight: 700; margin-top: 6px; }
        .stat-value.amber { color: var(--accent); }
        .stat-value.blue  { color: var(--accent2); }
 
        .color-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: middle; }
 
        .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .filters .form-control { max-width: 200px; }
 
        @media (max-width: 900px) {
            .sidebar { width: 60px; }
            .sidebar-logo span, .nav-section, .nav-link span { display: none; }
            .main { margin-left: 60px; padding: 20px; }
            .sidebar-user-info, .sidebar-user-name, .sidebar-user-email { display: none; }
        }
 
        /* ── Sidebar user block ── */
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .sidebar-user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #f5ad30);
            color: #000;
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: .85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-user-info { min-width: 0; flex: 1; }
        .sidebar-user-name  { font-size: .82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-email { font-size: .72rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .logout-btn {
            background: none; border: none; cursor: pointer;
            color: var(--muted); padding: 5px;
            border-radius: 6px; transition: color .15s, background .15s;
            display: flex;
        }
        .logout-btn:hover { color: var(--danger); background: rgba(239,68,68,.1); }
    </style>
    @stack('styles')
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-logo">Stream<span>Panel</span></div>
    <nav class="sidebar-nav">
        <div class="nav-section">Content</div>
        <a href="{{ route('channels.index') }}" class="nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            <span>Channels</span>
        </a>
        <a href="{{ route('sources.index') }}" class="nav-link {{ request()->routeIs('sources.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14M15.54 8.46a5 5 0 010 7.07M8.46 8.46a5 5 0 000 7.07"/></svg>
            <span>Sources</span>
        </a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            <span>Categories</span>
        </a>
        <a href="{{ route('tags.index') }}" class="nav-link {{ request()->routeIs('tags.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><circle cx="7" cy="7" r="1"/></svg>
            <span>Tags</span>
        </a>
        <div class="nav-section">Settings</div>
        <a href="{{ route('countries.index') }}" class="nav-link {{ request()->routeIs('countries.*') ? 'active' : '' }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
            <span>Countries</span>
        </a>
    </nav>
    {{-- User block at bottom of sidebar --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
            <div class="sidebar-user-email">{{ Auth::user()->email }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin-left:auto;flex-shrink:0">
            @csrf
            <button type="submit" class="logout-btn" title="Sign out">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    </div>
</aside>

<main class="main">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error) {{ $error }}<br> @endforeach
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>