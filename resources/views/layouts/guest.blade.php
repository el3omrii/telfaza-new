<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') — StreamPanel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bg font-sans text-gray-200 grid place-items-center p-5"
      style="background-image: linear-gradient(rgba(232,160,32,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(232,160,32,.03) 1px,transparent 1px);background-size:40px 40px">

<div class="w-full max-w-md">
    {{-- Brand --}}
    <div class="text-center mb-8">
        <div class="font-display font-extrabold text-3xl tracking-tight">Stream<span class="text-accent">Panel</span></div>
        <p class="text-muted text-sm mt-1.5">Admin Dashboard</p>
    </div>

    @yield('content')

    <p class="text-center text-muted text-xs mt-6">&copy; {{ date('Y') }} StreamPanel. All rights reserved.</p>
</div>

</body>
</html>
