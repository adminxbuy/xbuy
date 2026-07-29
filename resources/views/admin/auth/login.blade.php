<!DOCTYPE html>
<html lang="en" x-data="{ isDark: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': isDark }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</title>

    <link rel="icon" type="image/x-icon" href="{{ \App\Models\SiteSetting::getVal('website_favicon', '/favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-svh flex items-center justify-center bg-muted/40 p-6">

    <div class="w-full max-w-sm">
        <div class="rounded-xl border border-border bg-card shadow-sm p-8">
            <div class="flex flex-col items-center text-center mb-8">
                @if($logo = \App\Models\SiteSetting::getVal('website_logo'))
                    <img src="{{ $logo }}" class="h-10 w-auto max-w-[160px] object-contain mb-4" alt="Logo">
                @else
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-xl mb-4 bg-primary text-primary-foreground">
                        XB
                    </div>
                @endif
                <h1 class="text-xl font-semibold tracking-tight text-foreground">Admin Control Room</h1>
                <p class="text-sm text-muted-foreground mt-1">Sign in with your administrative credentials</p>
            </div>

            @if($errors->any())
                <div class="mb-6 p-3 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 text-sm font-medium dark:border-rose-800/50 dark:bg-rose-950/50 dark:text-rose-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-1.5">
                    <label for="email" class="text-sm font-medium text-foreground">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="text-sm font-medium text-foreground">Password</label>
                    <input type="password" id="password" name="password" required
                        class="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                </div>

                <label for="remember" class="flex items-center gap-2 text-sm text-muted-foreground select-none">
                    <input type="checkbox" id="remember" name="remember"
                        class="h-4 w-4 rounded border-input text-primary focus:ring-2 focus:ring-ring/50">
                    Remember this session
                </label>

                <button type="submit"
                    class="inline-flex w-full items-center justify-center h-10 rounded-lg bg-primary text-primary-foreground text-sm font-semibold shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Log in
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-muted-foreground mt-6">
            &copy; {{ date('Y') }} {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}. All rights reserved.
        </p>
    </div>

</body>

</html>
