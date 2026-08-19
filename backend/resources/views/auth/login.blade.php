<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="color-scheme" content="dark">
        <title>Sign in | BeeCore</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <main class="relative grid min-h-screen overflow-hidden lg:grid-cols-[minmax(0,1.1fr)_minmax(420px,0.9fr)]">
            <section class="relative hidden border-r border-white/10 lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16" aria-label="BeeCore platform">
                <div class="absolute inset-0 opacity-40" style="background-image: linear-gradient(rgba(45,212,191,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(45,212,191,.08) 1px, transparent 1px); background-size: 44px 44px"></div>
                <div class="relative flex items-center gap-3"><span class="grid h-10 w-10 place-items-center border border-teal-300/30 bg-teal-300/10 text-lg font-black text-teal-200" style="border-radius: 6px">B</span><span class="text-lg font-black tracking-wide text-white">BeeCore</span></div>
                <div class="relative max-w-xl"><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-300">ISP operations platform</p><h1 class="mt-5 text-5xl font-black leading-[1.05] text-white xl:text-6xl">Your network business, under control.</h1><p class="mt-6 max-w-lg text-base leading-7 text-slate-400">Manage customers, collections, billing, subscriptions, network inventory, and reseller operations from one workspace.</p></div>
                <p class="relative text-xs uppercase tracking-[0.16em] text-slate-600">Secure tenant-aware administration</p>
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-10 lg:px-14">
                <div class="w-full max-w-md">
                    <div class="mb-10 flex items-center gap-3 lg:hidden"><span class="grid h-10 w-10 place-items-center border border-teal-300/30 bg-teal-300/10 text-lg font-black text-teal-200" style="border-radius: 6px">B</span><span class="text-lg font-black text-white">BeeCore</span></div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-300">Workspace access</p>
                    <h1 class="mt-3 text-3xl font-black text-white">Sign in</h1>
                    <p class="mt-2 text-sm text-slate-500">Use your organization account to continue.</p>

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf
                        <div><label for="email" class="bc-label">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="bc-field" placeholder="you@company.com" aria-describedby="email-error">@error('email')<p id="email-error" class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label for="password" class="bc-label">Password</label><input id="password" type="password" name="password" required autocomplete="current-password" class="bc-field"></div>
                        <label class="inline-flex cursor-pointer items-center gap-3 text-sm text-slate-400"><input type="checkbox" name="remember" class="h-4 w-4 border-slate-600 bg-slate-950 text-teal-500 focus:ring-teal-500" style="border-radius: 4px"><span>Keep me signed in</span></label>
                        <button type="submit" class="bc-primary w-full justify-center py-3">Sign in securely</button>
                    </form>
                    <p class="mt-8 border-t border-white/10 pt-5 text-xs leading-5 text-slate-600">Access is restricted to authorized BeeCore workspace members.</p>
                </div>
            </section>
        </main>
    </body>
</html>
