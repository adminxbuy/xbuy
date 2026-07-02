<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">
    
    <!-- Fontshare Fonts (Cabinet Grotesk & Satoshi) -->
    <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@500,700,800&f[]=satoshi@300,400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS & App assets via Vite -->
    @vite(['resources/css/frontend.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Satoshi', 'Plus Jakarta Sans', sans-serif;
            background-color: #fafafa;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cabinet Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center items-center px-4 py-12 relative bg-zinc-50">

    <div class="max-w-md w-full space-y-6 bg-white p-8 sm:p-10 rounded-3xl border border-zinc-200/60 shadow-xl relative overflow-hidden">
        <!-- Accent Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full bg-yellow-100/40 blur-3xl"></div>
        
        <!-- Header Branding logo -->
        <div class="text-center space-y-4 relative z-10">
            <a href="/" class="inline-block">
                @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                    <img src="{{ $logo }}" class="h-9 w-auto object-contain mx-auto" alt="Logo">
                @else
                    <span class="font-bold text-2xl text-black tracking-tight">X-Buy</span>
                @endif
            </a>
            
            <div class="space-y-1.5">
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Forgot Password?</h1>
                <p class="text-zinc-500 text-xs font-medium leading-relaxed">Enter your registered email address below. We'll send you a 6-digit OTP code to verify and reset your password.</p>
            </div>
        </div>

        <!-- Session Notifications / Flash Alerts -->
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-250 text-emerald-800 rounded-[0.45rem] text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-250 text-rose-800 rounded-[0.45rem] text-xs font-semibold">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Forgot Password Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-5 relative z-10">
            @csrf
            
            <!-- Email input -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="e.g. name@domain.com"
                       class="w-full p-3.5 border border-zinc-200 rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-[#fdd835] hover:bg-[#e5c120] text-black font-semibold rounded-[0.45rem] text-sm transition-all shadow-md active:scale-[0.99] cursor-pointer">
                    Send OTP Verification Code
                </button>
            </div>
        </form>

        <!-- Back to login link -->
        <div class="pt-4 border-t border-zinc-100 text-center relative z-10">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-650 hover:underline flex items-center justify-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
                </svg>
                <span>Back to Log In</span>
            </a>
        </div>

    </div>

</body>
</html>
