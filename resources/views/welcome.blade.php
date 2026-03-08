<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MCBANKS Laravel') }} — professional stack</title>
    <!-- Tailwind + multi‑theme setup -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Initialize Tailwind dark mode config
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {},
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300..700&display=swap" rel="stylesheet">
    <!-- custom smooth theme transitions -->
    <style>
        * { transition: background-color 0.2s ease, border-color 0.2s ease; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        /* refined brand gradient — used sparingly for accent */
        .brand-accent {
            background: linear-gradient(145deg, #2563eb 0%, #4f46e5 100%);
        }
        .theme-toggle {
            background: rgba(0,0,0,0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .dark .theme-toggle {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.1);
        }
        /* card style – subtle, crisp, no heavy glass in light mode */
        .feature-card {
            @apply bg-white/80 dark:bg-gray-800/70 backdrop-blur-sm border border-gray-200/70 dark:border-gray-700/60 shadow-sm;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 antialiased selection:bg-blue-200 dark:selection:bg-blue-800 transition-colors duration-200">

    <!-- minimal theme toggle (light/dark/system respectful) -->
    <div class="absolute top-5 right-5 z-10 flex items-center gap-2">
        <button id="themeToggle" class="theme-toggle px-4 py-2 rounded-full text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm flex items-center gap-2 hover:scale-105 transition-transform duration-150" aria-label="Toggle theme">
            <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            <span id="themeLabel">Dark</span>
        </button>
    </div>

    <div class="min-h-screen flex items-center justify-center px-4 py-16">
        <div class="max-w-5xl w-full">
            <!-- header / hero unit – clean, professional -->
            <div class="text-center mb-14 space-y-5">
                <!-- small overline badge -->
                <span class="inline-block px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider brand-accent text-white shadow-md">laravel 12 · livewire 4 · spatie</span>
                
                <h1 class="text-5xl md:text-7xl font-black tracking-tight text-gray-900 dark:text-white mb-6">
                    {{ config('app.name', 'MCBANKS') }}<span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-blue-400">/Laravel</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-4xl mx-auto leading-relaxed font-medium">
                    Production‑ready Laravel starter with <span class="font-bold text-gray-900 dark:text-white">enterprise-grade authentication</span>, <span class="font-bold text-gray-900 dark:text-white">global geographic data</span>, and <span class="font-bold text-gray-900 dark:text-white">Kenyan administrative boundaries</span> — built for scalability.
                </p>

                <!-- status / session block -->
                @if(session('status'))
                    <div class="max-w-md mx-auto mt-6 p-4 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm font-medium shadow-sm">
                        ✅ {{ session('status') }}
                    </div>
                @endif

                <!-- cta buttons: refined, using solid/outline professional pairing -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                    @guest
                        <a href="{{ route('central.login') }}" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-md shadow-blue-500/20 transition-all hover:scale-[1.02] focus:ring-2 focus:ring-blue-400 dark:focus:ring-blue-600">
                            Sign in
                        </a>
                        <a href="{{ route('central.register') }}" class="px-8 py-3.5 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 text-gray-800 dark:text-gray-200 rounded-xl font-semibold shadow-sm transition-all hover:shadow-md hover:scale-[1.02]">
                            Create account
                        </a>
                    @else
                        @auth
                            @if(auth()->user()->hasRole('admin'))
                                <a href="/admin" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg transition">
                                    Admin Dashboard
                                </a>
                            @elseif(auth()->user()->hasRole('host'))
                                <a href="/host" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg transition">
                                    Host Dashboard
                                </a>
                            @else
                                <a href="/guest" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-lg transition">
                                    Guest Dashboard
                                </a>
                            @endif
                        @endauth
                    @endguest
                </div>
            </div>

            <!-- features grid – professional layout with better hierarchy -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
                <!-- Primary Features Row -->
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-6 right-6 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">🔐</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Role-Based Access Control</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Enterprise-grade permissions with Spatie Laravel package. Fine-grained access control for users, roles, and resources.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md font-medium">Security</span>
                    </div>
                </div>
                
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-6 right-6 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-green-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">🌍</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Global Geographic Data</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Comprehensive database with 250+ countries, 5,000+ states, and 150,000+ cities worldwide.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-md font-medium">Geography</span>
                    </div>
                </div>
                
                <div class="group relative bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-6 right-6 w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">⚡</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Livewire Components</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Dynamic, reactive UI components without JavaScript fatigue. Real-time updates with Laravel Livewire 4.1.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-md font-medium">Interactive</span>
                    </div>
                </div>
                
                <!-- Secondary Features Row -->
                <div class="group bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">🎨</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Modern UI/UX Design</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Professional Tailwind CSS design system with dark mode support and smooth animations.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-md font-medium">Design</span>
                    </div>
                </div>
                
                <div class="group bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">🇰🇪</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Kenyan Administrative Data</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Complete Kenyan data: 47 counties, 290+ constituencies, and 1,450+ wards.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-md font-medium">Local Data</span>
                    </div>
                </div>
                
                <div class="group bg-white dark:bg-gray-900 rounded-2xl p-7 shadow-lg border border-gray-100 dark:border-gray-800 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-2xl mb-5 shadow-lg">💰</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Currency & Localization</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-4">Multi-currency support with country-to-currency mapping and formatting helpers.</p>
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <span class="px-2 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-md font-medium">Finance</span>
                    </div>
                </div>
            </div>

            <!-- tech stack + github + stats bar – professional compact -->
            <div class="feature-card rounded-2xl p-7 flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
                <div class="flex flex-wrap items-center gap-3 justify-center md:justify-start">
                    <span class="text-xs font-semibold tracking-wider text-gray-500 dark:text-gray-400 uppercase">Stack</span>
                    <span class="px-3 py-1.5 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium border border-blue-200 dark:border-blue-800/50">Laravel 12</span>
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-700">Livewire 4.1</span>
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-700">Tailwind 4</span>
                    <span class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium border border-gray-200 dark:border-gray-700">PHP 8.3</span>
                    <span class="px-3 py-1.5 bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-300 rounded-full text-sm font-medium border border-purple-200 dark:border-purple-800/40">Spatie</span>
                </div>
                <a href="https://github.com/MCBANKSKE/MCBANKSLARAVEL" target="_blank" class="inline-flex items-center gap-2 px-5 py-2 rounded-full border border-gray-300 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition-all text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
                    <span>GitHub · v1.0.2</span>
                </a>
            </div>

            <!-- footer note -->
            <div class="text-center text-sm text-gray-500 dark:text-gray-500 mt-8 border-t border-gray-200 dark:border-gray-800 pt-6">
                <p>© {{ date('Y') }} MCBANKS. MIT licensed · built for scalability and clarity · 
                    <a href="#" class="underline decoration-dotted underline-offset-4 hover:text-blue-600 dark:hover:text-blue-400">docs</a>
                </p>
            </div>
        </div>
    </div>

    <!-- theme switcher script (remembers preference) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeLabel = document.getElementById('themeLabel');
            const themeIcon = document.getElementById('themeIcon');
            
            if (!themeToggle || !themeLabel || !themeIcon) {
                console.error('Theme toggle elements not found');
                return;
            }
            
            // get stored or system preference
            const stored = localStorage.getItem('theme');
            const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            function setTheme(theme) {
                console.log('Setting theme to:', theme);
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    themeLabel.innerText = 'Light';
                    themeIcon.innerHTML = '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>';  // moon
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    themeLabel.innerText = 'Dark';
                    themeIcon.innerHTML = '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>'; // sun
                }
            }
            
            // initial theme setup
            if (stored === 'dark') {
                setTheme('dark');
            } else if (stored === 'light') {
                setTheme('light');
            } else {
                setTheme(systemDark ? 'dark' : 'light');
            }
            
            // theme toggle event listener
            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isDark = document.documentElement.classList.contains('dark');
                setTheme(isDark ? 'light' : 'dark');
            });
        });
    </script>
</body>
</html>