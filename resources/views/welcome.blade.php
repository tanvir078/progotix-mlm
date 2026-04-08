<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'ProgotiX') }} MLM</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @unless (app()->runningUnitTests())
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endunless
    </head>
    <body class="min-h-screen bg-zinc-950 text-white">
        <div class="relative isolate overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.35),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(34,211,238,0.25),_transparent_35%)]"></div>

            <header class="relative mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
                <div>
                    <p class="text-sm uppercase tracking-[0.35em] text-emerald-300/80">ProgotiX</p>
                    <p class="mt-1 text-lg font-semibold">MLM Management Suite</p>
                </div>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full border border-white/15 px-5 py-2.5 text-sm font-medium text-white/90 transition hover:bg-white/10">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-5 py-2.5 text-sm font-medium text-white/90 transition hover:bg-white/10">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-100">
                            Create account
                        </a>
                    @endauth
                </nav>
            </header>

            <main class="relative mx-auto grid min-h-[calc(100vh-88px)] w-full max-w-7xl items-center gap-10 px-6 pb-16 pt-8 lg:grid-cols-[1.15fr_0.85fr] lg:px-10 lg:pb-24">
                <section class="max-w-3xl">
                    <div class="inline-flex rounded-full border border-emerald-400/30 bg-emerald-400/10 px-4 py-2 text-sm text-emerald-200">
                        Referral tracking, package activation, commission ledger
                    </div>
                    <h1 class="mt-6 text-5xl font-semibold tracking-tight text-white sm:text-6xl">
                        এমএলএম সফটওয়্যার যেটা দল, আয় আর growth একসাথে manage করে
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-300">
                        ProgotiX আপনাকে direct referral, package subscription, sponsor commission, আর network visibility
                        এক dashboard-এ দেয়, যাতে আপনার MLM operation clean এবং measurable থাকে।
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-100">
                                Open dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-100">
                                Start with referral link
                            </a>
                            <a href="{{ route('login') }}" class="rounded-full border border-white/15 px-6 py-3 text-sm font-medium text-white/90 transition hover:bg-white/10">
                                Member login
                            </a>
                        @endauth
                    </div>

                    <div class="mt-14 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-sm text-zinc-400">Referral Network</p>
                            <p class="mt-2 text-2xl font-semibold">Direct + Downline</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-sm text-zinc-400">Package Engine</p>
                            <p class="mt-2 text-2xl font-semibold">Starter to Leader</p>
                        </div>
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                            <p class="text-sm text-zinc-400">Commission Ledger</p>
                            <p class="mt-2 text-2xl font-semibold">Transparent Earnings</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-emerald-950/30 backdrop-blur">
                    <div class="grid gap-4">
                        <div class="rounded-3xl bg-zinc-900/80 p-5">
                            <p class="text-sm text-zinc-400">Dashboard cards</p>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <p class="text-xs uppercase tracking-[0.25em] text-zinc-500">Wallet</p>
                                    <p class="mt-2 text-2xl font-semibold">৳ 12,450</p>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <p class="text-xs uppercase tracking-[0.25em] text-zinc-500">Team Size</p>
                                    <p class="mt-2 text-2xl font-semibold">128</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl bg-linear-to-br from-emerald-500/15 to-cyan-500/15 p-5">
                            <p class="text-sm text-zinc-300">Why it matters</p>
                            <ul class="mt-4 space-y-3 text-sm leading-6 text-zinc-200">
                                <li>Referral username দিয়ে নতুন member onboarding</li>
                                <li>Package activation করলে sponsor bonus auto-credit</li>
                                <li>Commission history clearly traceable</li>
                                <li>Admin/demo data সহ দ্রুত launch-ready foundation</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
