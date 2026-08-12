<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CSV Import') &middot; Mayank Rathod - Shopify CSV Product Import</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="site-header__inner">
            <a href="{{ route('uploads.create') }}" class="brand">Mayank Rathod - Shopify CSV Product Import</a>
            <nav class="main-nav">
                <a href="{{ route('uploads.create') }}" class="{{ request()->routeIs('uploads.create') ? 'active' : '' }}">Upload</a>
                <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="container">
        @if (session('success'))
            <div class="flash flash--success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="flash flash--error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
