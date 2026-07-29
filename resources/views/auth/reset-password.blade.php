<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>Reset Password | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
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
 <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Reset Password</h1>
 <p class="text-zinc-500 text-xs font-medium leading-relaxed">Enter the 6-digit verification code sent to <span class="text-zinc-850 font-semibold font-mono">{{ $email }}</span> and set your new account password.</p>
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

 <!-- Reset Password Form -->
 <form action="{{ route('password.update') }}" method="POST" class="space-y-5 relative z-10">
 @csrf
  <!-- Email (Hidden) -->
 <input type="hidden" name="email" value="{{ $email }}">

 <!-- OTP Input -->
 <div class="space-y-1.5">
 <label for="otp" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Verification OTP Code</label>
 <input type="text" id="otp" name="otp" required maxlength="6" placeholder="Enter 6-digit code" autocomplete="off"
 class="w-full p-3.5 border border-zinc-200 rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-bold font-mono text-center tracking-[0.4em] transition-all">
 </div>

 <!-- New Password with eye toggle inside Alpine.js context -->
 <div class="space-y-1.5" x-data="{ show: false }">
 <label for="password" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">New Password</label>
 <div class="relative flex items-center">
 <input :type="show ? 'text' : 'password'" id="password" name="password" required placeholder="Use at least 8 characters"
 class="w-full p-3.5 pr-12 border border-zinc-200 rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
 <button type="button" @click="show = !show" class="absolute right-4 text-zinc-400 hover:text-zinc-700 transition-colors focus:outline-none" aria-label="Toggle password view">
 <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3.15 3.15m-3.15-3.15-6-6m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"></path>
 </svg>
 <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display: none;">
 <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
 <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"></circle>
 </svg>
 </button>
 </div>
 </div>

 <!-- Confirm Password -->
 <div class="space-y-1.5">
 <label for="password_confirmation" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Confirm New Password</label>
 <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Re-enter your new password"
 class="w-full p-3.5 border border-zinc-200 rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
 </div>

 <!-- Submit Button -->
 <div class="pt-2">
 <button type="submit" class="w-full py-3.5 bg-[#fdd835] hover:bg-[#e5c120] text-black font-semibold rounded-[0.45rem] text-sm transition-all shadow-md active:scale-[0.99] cursor-pointer">
 Verify and Reset Password
 </button>
 </div>
 </form>

 <!-- Back to login link -->
 <div class="pt-4 border-t border-zinc-100 text-center relative z-10">
 <a href="{{ route('login') }}" class="text-xs font-semibold text-blue-655 hover:underline flex items-center justify-center space-x-1.5">
 <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"></path>
 </svg>
 <span>Back to Log In</span>
 </a>
 </div>

 </div>

</body>
</html>
