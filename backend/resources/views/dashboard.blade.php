<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>BeeCore Dashboard</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-slate-950 text-slate-100 antialiased">
        <div class="min-h-screen">
            <aside class="fixed inset-y-0 left-0 w-72 border-r border-slate-800 bg-slate-900/95 p-6">
                <div class="mb-10">
                    <div class="text-2xl font-black tracking-tight text-cyan-400">BeeCore</div>
                    <div class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-500">SaaS Control</div>
                </div>

                <nav class="space-y-2 text-sm text-slate-300">
                    <a href="#" class="flex items-center justify-between rounded-xl bg-cyan-500/15 px-3 py-2 text-cyan-300"><span>Overview</span><span>●</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Customers</span><span>12K</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Billing</span><span>£</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Payments</span><span>↗</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Network</span><span>◉</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Resellers</span><span>◎</span></a>
                    <a href="#" class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-slate-800"><span>Reports</span><span>▣</span></a>
                </nav>
            </aside>

            <main class="ml-72 p-8">
                <header class="mb-8 flex items-center justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Dashboard</p>
                        <h1 class="mt-2 text-3xl font-black text-white">ISP performance overview</h1>
                    </div>
                    <div class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-sm text-slate-200">
                        Last sync: 2 minutes ago
                    </div>
                </header>

                <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-sm text-slate-400">Active tenants</p>
                        <p class="mt-3 text-3xl font-black text-white">{{ $metrics['tenants'] }}</p>
                        <p class="mt-2 text-xs text-emerald-400">+12% vs last month</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-sm text-slate-400">Customers</p>
                        <p class="mt-3 text-3xl font-black text-white">{{ number_format($metrics['customers']) }}</p>
                        <p class="mt-2 text-xs text-cyan-400">+384 new this week</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-sm text-slate-400">Monthly revenue</p>
                        <p class="mt-3 text-3xl font-black text-white">৳{{ number_format($metrics['monthly_revenue']) }}</p>
                        <p class="mt-2 text-xs text-violet-400">Cash collection steady</p>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                        <p class="text-sm text-slate-400">SMS usage</p>
                        <p class="mt-3 text-3xl font-black text-white">{{ number_format($metrics['sms_usage']) }}</p>
                        <p class="mt-2 text-xs text-amber-400">Low stock alerts</p>
                    </div>
                </section>

                <section class="mt-8 grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-white">Operational snapshot</h2>
                            <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-300">Healthy</span>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm text-slate-300"><span>Active services</span><span>{{ $metrics['active_services'] }}%</span></div>
                                <div class="h-2.5 rounded-full bg-slate-800"><div class="h-2.5 rounded-full bg-cyan-400" style="width: 96%"></div></div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm text-slate-300"><span>Pending billing</span><span>{{ $metrics['pending_billing'] }}</span></div>
                                <div class="h-2.5 rounded-full bg-slate-800"><div class="h-2.5 rounded-full bg-violet-400" style="width: 35%"></div></div>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between text-sm text-slate-300"><span>Network uptime</span><span>99.8%</span></div>
                                <div class="h-2.5 rounded-full bg-slate-800"><div class="h-2.5 rounded-full bg-emerald-400" style="width: 99.8%"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6">
                        <h2 class="text-xl font-bold text-white">Quick actions</h2>
                        <div class="mt-5 space-y-3">
                            <button class="w-full rounded-xl bg-cyan-500 px-4 py-3 font-semibold text-slate-950 hover:bg-cyan-400">Create customer</button>
                            <button class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 font-semibold text-white hover:border-slate-500">Generate invoice</button>
                            <button class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 font-semibold text-white hover:border-slate-500">Run network sync</button>
                            <button class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 font-semibold text-white hover:border-slate-500">Send payment reminder</button>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
