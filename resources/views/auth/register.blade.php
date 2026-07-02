<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign up | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">
    
    <!-- Fontshare Fonts (Cabinet Grotesk & Satoshi) -->
    <link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@500,700,800&f[]=satoshi@300,400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS & App assets via Vite -->
    @vite(['resources/css/frontend.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Satoshi', 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cabinet Grotesk', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-950 flex flex-col justify-between">

    <div class="flex-1 flex min-h-screen">
        
        <!-- Left Side Panel (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-zinc-50 border-r border-zinc-100 flex-col justify-between p-16 select-none relative overflow-hidden">
            <!-- Background Accent Circles -->
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-yellow-100/40 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-blue-50/50 blur-3xl"></div>
            
            <div class="relative z-10">
                <!-- Branding logo/link -->
                <a href="/" class="flex items-center space-x-2">
                    @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                        <img src="{{ $logo }}" class="h-10 w-auto object-contain" alt="{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}">
                    @else
                        <span class="font-bold text-3xl text-black tracking-tight">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</span>
                    @endif
                </a>
            </div>

            <!-- Illustration & Tagline Group -->
            <div class="my-auto space-y-8 relative z-10 flex flex-col items-center text-center">
                <div class="max-w-md">
                    <h2 class="text-4xl font-bold tracking-tight text-zinc-900 leading-tight">
                        Buy or sell <br>any item, anytime
                    </h2>
                    <p class="text-zinc-500 mt-3 text-sm font-medium">India's Safest PC Parts Escrow Marketplace.</p>
                </div>
                
                <!-- Visual Illustration Banner -->
                <div class="w-full max-w-sm">
                    <img src="/website_assets/images/login.png" class="w-full h-auto" alt="Login Banner">
                </div>
            </div>

            <div class="text-xs text-zinc-400 font-medium relative z-10">
                &copy; {{ date('Y') }} {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}. All rights reserved.
            </div>
        </div>

        <!-- Right Side Panel (Form) -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-6 py-12 sm:px-12 lg:px-20 relative bg-white">
            
            <!-- Mobile Brand Header (Visible only on Mobile) -->
            <div class="lg:hidden absolute top-8 left-8">
                <a href="/">
                    @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                        <img src="{{ $logo }}" class="h-8 w-auto object-contain" alt="Logo">
                    @else
                        <span class="font-bold text-2xl text-black tracking-tight">X-Buy</span>
                    @endif
                </a>
            </div>

            <div class="max-w-md w-full space-y-7">
                <!-- Heading -->
                <div class="space-y-1.5">
                    <h1 class="text-3xl font-bold text-zinc-900 tracking-tight">Sign up to continue</h1>
                </div>

                <!-- Session Notifications / Flash Alerts -->
                @if ($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('password') && !$errors->has('terms') && !$errors->has('social'))
                    <div class="p-4 bg-rose-50 border border-rose-250 text-rose-800 rounded-[0.45rem] text-xs font-semibold">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Dynamic Social Logins Area -->
                <div class="space-y-3">
                    @if(\App\Models\SiteSetting::getVal('google_login_enabled'))
                        <a href="{{ route('social.redirect', 'google') }}" 
                           class="w-full py-3 px-4 border border-zinc-200 hover:bg-zinc-50 rounded-[0.45rem] text-sm font-semibold flex items-center justify-center space-x-2.5 transition-all text-zinc-700">
                            <!-- Google G SVG Logo -->
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.85z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.85c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            <span>Continue with Google</span>
                        </a>
                    @endif

                    @if(\App\Models\SiteSetting::getVal('facebook_login_enabled'))
                        <a href="{{ route('social.redirect', 'facebook') }}" 
                           class="w-full py-3 px-4 bg-[#1877f2] hover:bg-[#166fe5] text-white rounded-[0.45rem] text-sm font-semibold flex items-center justify-center space-x-2.5 transition-all">
                            <!-- Facebook SVG Logo -->
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Continue with Facebook</span>
                        </a>
                    @endif

                    @if(\App\Models\SiteSetting::getVal('apple_login_enabled'))
                        <a href="{{ route('social.redirect', 'apple') }}" 
                           class="w-full py-3 px-4 bg-black hover:bg-zinc-900 text-white rounded-[0.45rem] text-sm font-semibold flex items-center justify-center space-x-2.5 transition-all">
                            <!-- Apple SVG Logo -->
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.22.67-2.94 1.5-.63.73-1.18 1.87-1.03 2.97 1.12.09 2.27-.56 2.98-1.41z"/>
                            </svg>
                            <span>Continue with Apple</span>
                        </a>
                    @endif

                    @if($errors->has('social'))
                        <p class="text-xs text-red-600 font-semibold text-center mt-1">{{ $errors->first('social') }}</p>
                    @endif
                </div>

                <!-- Subtle separator line -->
                @if(\App\Models\SiteSetting::getVal('google_login_enabled') || \App\Models\SiteSetting::getVal('facebook_login_enabled') || \App\Models\SiteSetting::getVal('apple_login_enabled'))
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-zinc-200"></div>
                        <span class="flex-shrink mx-4 text-xs font-semibold text-zinc-400 uppercase tracking-wider">or</span>
                        <div class="flex-grow border-t border-zinc-200"></div>
                    </div>
                @endif

                <!-- Email/Password Signup Form -->
                <form action="/register" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Name input -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter your full name"
                               class="w-full p-3.5 border @error('name') border-red-500 @else border-zinc-200 @enderror rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
                        @error('name')
                            <p class="text-xs text-red-655 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email input -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="Enter your email"
                               class="w-full p-3.5 border @error('email') border-red-500 @else border-zinc-200 @enderror rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
                        @error('email')
                            <p class="text-xs text-red-655 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password input with eye toggle inside Alpine.js context -->
                    <div class="space-y-1.5" x-data="{ show: false }">
                        <label for="password" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Password</label>
                        <div class="relative flex items-center">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required placeholder="Use at least 8 characters"
                                   class="w-full p-3.5 pr-12 border @error('password') border-red-500 @else border-zinc-200 @enderror rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
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
                        @error('password')
                            <p class="text-xs text-red-655 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Re-enter your password"
                               class="w-full p-3.5 border border-zinc-200 rounded-[0.45rem] text-sm focus:ring-2 focus:ring-[#fdd835] focus:border-[#fdd835] focus:outline-none bg-white placeholder-zinc-400 font-medium transition-all">
                    </div>

                    <!-- Terms agreement checkbox -->
                    <div class="pt-1.5">
                        <label class="flex items-start cursor-pointer select-none">
                            <input type="checkbox" name="terms" value="1" required class="sr-only peer">
                            <div class="w-5 h-5 bg-zinc-200 peer-focus:outline-none rounded-md peer peer-checked:after:content-['✓'] peer-checked:after:text-black peer-checked:after:font-bold peer-checked:after:text-xs peer-checked:after:flex peer-checked:after:items-center peer-checked:after:justify-center peer-checked:bg-[#fdd835] flex-shrink-0 mt-0.5 transition-all"></div>
                            <span class="ml-2.5 text-xs text-zinc-500 font-medium leading-relaxed">
                                I agree to the 
                                <a href="/v1/pages/{{ \App\Models\SiteSetting::getVal('terms_page_slug', 'terms-of-service') }}" target="_blank" class="underline hover:text-zinc-800">Terms of Service</a> 
                                and 
                                <a href="/v1/pages/{{ \App\Models\SiteSetting::getVal('privacy_page_slug', 'privacy-policy') }}" target="_blank" class="underline hover:text-zinc-800">Privacy Policy</a>.
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-xs text-red-655 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-3.5 bg-[#fdd835] hover:bg-[#e5c120] text-black font-semibold rounded-[0.45rem] text-sm transition-all shadow-md active:scale-[0.99] cursor-pointer">
                            Sign up
                        </button>
                    </div>
                </form>

                <!-- Footer and redirection -->
                <div class="pt-4 border-t border-zinc-100 flex flex-col items-center space-y-3.5 text-center">
                    <p class="text-[10px] leading-relaxed text-zinc-455 max-w-sm">
                        We keep your information safe. We never sell your personal credentials outside the platform operations scope.
                    </p>
                    <p class="text-sm font-medium text-zinc-650">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-blue-655 hover:underline">Log in</a>
                    </p>
                </div>

            </div>

        </div>

    </div>

</body>
</html>
