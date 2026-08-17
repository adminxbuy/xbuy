<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title') | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
 <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">
  <!-- Google Fonts (Inter) -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Tailwind CSS (Vite) -->
 @vite(['resources/css/frontend.css', 'resources/js/app.js'])
  <!-- Lucide Icons -->
 <script src="https://unpkg.com/lucide@latest"></script>

 <style>
 [x-cloak] {
 display: none !important;
 }
 body {
 font-family: 'Inter', sans-serif;
 background-color: #fafafa;
 color: #18181b;
 }
 h1, h2, h3, h4, h5, h6 {
 font-family: 'Inter', sans-serif;
 }
 .scrollbar-none {
 -ms-overflow-style: none;
 scrollbar-width: none;
 }
 .scrollbar-none::-webkit-scrollbar {
 display: none;
 }
 </style>
</head>
<body class="min-h-screen flex flex-col bg-zinc-50 text-zinc-900 selection:bg-yellow-200">

 <!-- Global Header -->
 @include('layouts.partials.header')

 <!-- Main Content -->
 <main class="flex-1">
 @yield('content')
 </main>

 <!-- Global Footer -->
 @include('layouts.partials.footer')


 <!-- Initialize Lucide Icons -->
 <script>
 lucide.createIcons();
 </script>
</body>
</html>