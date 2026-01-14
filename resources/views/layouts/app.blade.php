<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sovereign Control Plane</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 text-gray-800 dark:text-gray-200 min-h-screen transition-colors duration-200">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo/Brand -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center shadow-md">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            Sovereign Control
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Secure Governance Platform</p>
                    </div>
                </div>

                @auth
                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 flex items-center space-x-2 group">
                        <i class="fas fa-tachometer-alt text-gray-500 dark:text-gray-400 group-hover:text-blue-500"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    @role('admin')
                    <a href="{{ route('governance.index') }}" 
                       class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 flex items-center space-x-2 group">
                        <i class="fas fa-balance-scale text-gray-500 dark:text-gray-400 group-hover:text-purple-500"></i>
                        <span class="font-medium">Governance</span>
                    </a>
                    @endrole
                    
                    @role('oracle')
                    <a href="{{ route('scenarios.index') }}" 
                       class="px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200 flex items-center space-x-2 group">
                        <i class="fas fa-brain text-gray-500 dark:text-gray-400 group-hover:text-green-500"></i>
                        <span class="font-medium">Scenarios</span>
                    </a>
                    @endrole
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="font-medium text-sm">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->role }}</p>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200 z-50">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                                <p class="font-medium">{{ Auth::user()->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('logout') }}" 
                               class="flex items-center space-x-2 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-b-xl transition-colors">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="md:hidden fixed inset-x-0 top-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-lg transform -translate-y-full transition-transform duration-300 z-40">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                <i class="fas fa-tachometer-alt text-blue-500"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            
            @role('admin')
            <a href="{{ route('governance.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                <i class="fas fa-balance-scale text-purple-500"></i>
                <span class="font-medium">Governance</span>
            </a>
            @endrole
            
            @role('oracle')
            <a href="{{ route('scenarios.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
                <i class="fas fa-brain text-green-500"></i>
                <span class="font-medium">Scenarios</span>
            </a>
            @endrole
            
            <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('logout') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-6">
        <!-- Notifications -->
        @if(session('status'))
            <div class="mb-6 animate-slide-down">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-green-800 dark:text-green-300">Success</p>
                            <p class="text-sm text-green-700 dark:text-green-400">{{ session('status') }}</p>
                        </div>
                        <button class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 animate-slide-down">
                <div class="bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-red-800 dark:text-red-300">Error</p>
                            <p class="text-sm text-red-700 dark:text-red-400">{{ session('error') }}</p>
                        </div>
                        <button class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-sm p-6">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t border-gray-200/50 dark:border-gray-700/50">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 rounded-md bg-gradient-to-br from-blue-600 to-purple-600 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-xs"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        © 2024 Sovereign Control. All rights reserved.
                    </p>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Privacy</a>
                    <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Terms</a>
                    <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Support</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
    
    <!-- JavaScript for interactions -->
    <script>
        // Theme toggle
        document.getElementById('themeToggle').addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        });

        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('-translate-y-full');
        });

        // Close notifications
        document.querySelectorAll('[class*="bg-gradient-to-r"] button .fa-times').forEach(button => {
            button.parentElement.addEventListener('click', function() {
                this.closest('.animate-slide-down').remove();
            });
        });

        // Initialize theme from localStorage
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    
    <style>
        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .dark ::-webkit-scrollbar-track {
            background: #1f2937;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .dark ::-webkit-scrollbar-thumb {
            background: #4b5563;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
</body>
</html>