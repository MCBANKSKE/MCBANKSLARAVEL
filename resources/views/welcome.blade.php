<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'MCBANKS Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                    {{ config('app.name', 'MCBANKS Laravel') }}
                </h1>
                <p class="text-xl text-white/90 mb-8">
                    Laravel starter with role-based authentication, geographical data, and modern UI
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @guest
                        <a href="{{ route('login') }}" class="px-8 py-3 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="px-8 py-3 glass-effect text-white rounded-lg font-semibold hover:bg-white/20 transition-colors">
                            Register
                        </a>
                    @else
                        <a href="/dashboard" class="px-8 py-3 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">🔐</div>
                    <h3 class="text-xl font-semibold mb-2">Role-Based Auth</h3>
                    <p class="text-white/80 text-sm">Complete authentication system with Spatie permissions and role management</p>
                </div>
                
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">🌍</div>
                    <h3 class="text-xl font-semibold mb-2">Geographical Data</h3>
                    <p class="text-white/80 text-sm">250+ countries, 5,000+ states, 150,000+ cities with Kenyan administrative data</p>
                </div>
                
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">⚡</div>
                    <h3 class="text-xl font-semibold mb-2">Livewire Components</h3>
                    <p class="text-white/80 text-sm">Dynamic forms and interactions without writing JavaScript</p>
                </div>
                
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">🎨</div>
                    <h3 class="text-xl font-semibold mb-2">Modern UI</h3>
                    <p class="text-white/80 text-sm">Beautiful Tailwind CSS design with glass morphism effects</p>
                </div>
                
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">🇰🇪</div>
                    <h3 class="text-xl font-semibold mb-2">Kenyan Data</h3>
                    <p class="text-white/80 text-sm">47 counties, 290+ constituencies, 1,450+ wards included</p>
                </div>
                
                <div class="glass-effect rounded-xl p-6 text-white">
                    <div class="text-3xl mb-4">💰</div>
                    <h3 class="text-xl font-semibold mb-2">Currency Support</h3>
                    <p class="text-white/80 text-sm">Integrated currency information for all countries</p>
                </div>
            </div>

            <!-- Tech Stack -->
            <div class="glass-effect rounded-xl p-6 text-white">
                <h3 class="text-xl font-semibold mb-4 text-center">Built With</h3>
                <div class="flex flex-wrap justify-center gap-3">
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Laravel 12</span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Livewire 4.1</span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Tailwind CSS</span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">Spatie Permissions</span>
                    <span class="px-3 py-1 bg-white/20 rounded-full text-sm">PHP 8.2+</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 text-white/70 text-sm">
                <p>Version 1.0.2 • 
                    <a href="https://github.com/MCBANKSKE/MCBANKSLARAVEL" class="hover:text-white transition-colors" target="_blank">
                        GitHub
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>