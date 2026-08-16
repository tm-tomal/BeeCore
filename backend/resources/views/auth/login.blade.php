<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BeeCore Login</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <div class="flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl shadow-cyan-900/20">
                <div class="mb-8 text-center">
                    <div class="inline-flex items-center justify-center rounded-2xl bg-cyan-500/15 px-4 py-2 text-sm font-semibold uppercase tracking-[0.22em] text-cyan-300">
                        BeeCore
                    </div>
                    <h1 class="mt-6 text-3xl font-black text-white">Sign in</h1>
                    <p class="mt-2 text-sm text-slate-400">Access your ISP control panel</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none" placeholder="admin@beecore.test">
                        @error('email')
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                        <input id="password" type="password" name="password" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none" placeholder="••••••••">
                    </div>

                    <div class="flex items-center justify-between text-sm text-slate-400">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-600 bg-slate-950 text-cyan-500 focus:ring-cyan-500">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="text-cyan-400 hover:text-cyan-300">Forgot password?</a>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-cyan-500 px-4 py-3 font-semibold text-slate-950 transition hover:bg-cyan-400">
                        Log in
                    </button>
                </form>

                <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-950/70 p-4 text-sm text-slate-400">
                    Demo account: <span class="font-medium text-slate-200">admin@beecore.test</span> / <span class="font-medium text-slate-200">password123</span>
                </div>
            </div>
        </div>
    </body>
</html>
