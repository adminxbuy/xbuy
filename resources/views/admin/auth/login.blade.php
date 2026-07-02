<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | X-Buy.in</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-50 min-h-screen flex items-center justify-center p-6"
    style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div class="w-full max-w-md bg-white border border-zinc-200 rounded-xl shadow-xl overflow-hidden p-8">
        <div class="flex flex-col items-center mb-8">
            <div class="w-12 h-12 bg-zinc-900 text-white hover:bg-zinc-800 rounded-xl flex items-center justify-center font-bold text-black border border-black/10 shadow-md mb-4 text-xl"
                style="font-family: 'Outfit', sans-serif;">XB</div>
            <h1 class="text-2xl font-bold text-zinc-900 tracking-tight" style="font-family: 'Outfit', sans-serif;">Admin
                Control Room</h1>
            <p class="text-sm text-zinc-500 mt-1">Sign in with administrative credentials</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Email
                    Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent transition-all">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password"
                        class="block text-xs font-semibold uppercase tracking-wider text-zinc-500">Password</label>
                </div>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent transition-all">
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember"
                    class="w-4 h-4 text-[#09090b] border-zinc-300 rounded focus:ring-zinc-950">
                <label for="remember" class="ml-2 text-sm text-zinc-600">Remember session</label>
            </div>

            <button type="submit"
                class="w-full bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-3.5 px-4 rounded-xl shadow-md transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 border border-black/10">
                Log In
            </button>
        </form>
    </div>

</body>

</html>