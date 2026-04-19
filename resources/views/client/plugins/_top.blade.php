<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('meta_description', 'HaloSitek - The definitive architectural ledger for modern developers and luxury estate curators.')">
<title>@yield('title', 'HaloSitek - Architectural Ledger')</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
<link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

@stack('styles')
