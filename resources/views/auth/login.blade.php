@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 shadow-lg mb-4">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Welcome Back</h1>
            <p class="text-gray-600 dark:text-gray-400">Sign in to your Sovereign Control account</p>
        </div>

        <!-- Login Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl p-8">
            <form method="POST" action="{{ url('/login') }}" id="loginForm">
                @csrf
                
                <!-- Email Field -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center justify-between">
                            <span>Email Address</span>
                            <i class="fas fa-at text-gray-400 text-sm"></i>
                        </div>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('email') border-red-500 ring-2 ring-red-200 @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <div class="mt-2 flex items-center text-sm text-red-600 dark:text-red-400">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <div class="flex items-center justify-between">
                            <span>Password</span>
                            <button type="button" onclick="togglePassword()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" type="password" name="password" required
                               class="w-full pl-10 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('password') border-red-500 ring-2 ring-red-200 @enderror"
                               placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button type="button" onclick="togglePassword()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-eye" id="passwordToggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    @error('password')
                        <div class="mt-2 flex items-center text-sm text-red-600 dark:text-red-400">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="remember" name="remember" 
                                   class="sr-only peer" {{ old('remember') ? 'checked' : '' }}>
                            <div class="w-5 h-5 bg-gray-200 dark:bg-gray-700 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-md peer peer-checked:bg-gradient-to-r peer-checked:from-blue-500 peer-checked:to-blue-600">
                                <svg class="w-5 h-5 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Remember me</span>
                    </label>
                    
                    <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        Forgot password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitButton"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span id="buttonText">Sign In</span>
                    <i class="fas fa-spinner fa-spin ml-2 hidden" id="loadingIcon"></i>
                </button>

                <!-- Demo Accounts Hint -->
                <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/50 dark:to-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 flex items-center justify-center">
                                <i class="fas fa-lightbulb text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Demo Accounts</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Try: <code class="bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded text-xs">admin@example.com</code> or <code class="bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded text-xs">oracle@example.com</code>
                            </p>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Alternative Auth Options -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">Or continue with</p>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('auth.google') }}" 
                       class="flex items-center justify-center space-x-2 py-2.5 px-4 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors hover:border-red-400 dark:hover:border-red-500 group">
                        <i class="fab fa-google text-red-500 group-hover:text-red-600"></i>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Google</span>
                    </a>
                    <a href="{{ route('auth.github') }}" 
                       class="flex items-center justify-center space-x-2 py-2.5 px-4 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors hover:border-gray-400 dark:hover:border-gray-500 group">
                        <i class="fab fa-github text-gray-800 dark:text-white group-hover:text-gray-900 dark:group-hover:text-gray-100"></i>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">GitHub</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sign Up Link -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Don't have an account?
                <a href="#" class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                    Request access
                </a>
            </p>
        </div>

        <!-- Footer Note -->
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Secure authentication powered by Laravel Sanctum
                <br>
                <span class="text-gray-400 dark:text-gray-600">v{{ config('app.version', '1.0.0') }}</span>
            </p>
        </div>
    </div>
</div>

<!-- Success/Error Toast -->
<div id="authToast" class="fixed top-6 right-6 z-50 hidden">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 max-w-sm">
        <div class="flex items-center space-x-3">
            <div id="toastIcon" class="flex-shrink-0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
            </div>
            <div class="flex-1">
                <p id="toastTitle" class="font-medium text-gray-900 dark:text-white"></p>
                <p id="toastMessage" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
            </div>
            <button onclick="hideToast()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('passwordToggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form submission with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitButton = document.getElementById('submitButton');
    const buttonText = document.getElementById('buttonText');
    const loadingIcon = document.getElementById('loadingIcon');
    
    // Disable button and show loading
    submitButton.disabled = true;
    buttonText.textContent = 'Signing In...';
    loadingIcon.classList.remove('hidden');
    
    // Validate form
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        showToast('Please fill all fields', 'error');
        resetButtonState();
        return;
    }
    
    // If validation passes, form will submit normally
});

function resetButtonState() {
    const submitButton = document.getElementById('submitButton');
    const buttonText = document.getElementById('buttonText');
    const loadingIcon = document.getElementById('loadingIcon');
    
    setTimeout(() => {
        submitButton.disabled = false;
        buttonText.textContent = 'Sign In';
        loadingIcon.classList.add('hidden');
    }, 2000);
}

// Toast notification system
function showToast(message, type = 'info') {
    const toast = document.getElementById('authToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    // Set content
    if (type === 'error') {
        toastTitle.textContent = 'Error';
        toastIcon.innerHTML = '<div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center"><i class="fas fa-exclamation-circle text-red-600 dark:text-red-400"></i></div>';
    } else if (type === 'success') {
        toastTitle.textContent = 'Success';
        toastIcon.innerHTML = '<div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center"><i class="fas fa-check-circle text-green-600 dark:text-green-400"></i></div>';
    } else {
        toastTitle.textContent = 'Info';
        toastIcon.innerHTML = '<div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"><i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i></div>';
    }
    
    toastMessage.textContent = message;
    
    // Show toast with animation
    toast.classList.remove('hidden');
    toast.style.animation = 'slideInRight 0.3s ease-out';
    
    // Auto-hide after 5 seconds
    setTimeout(hideToast, 5000);
}

function hideToast() {
    const toast = document.getElementById('authToast');
    toast.style.animation = 'slideOutRight 0.3s ease-out';
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 300);
}

// Show errors from server if they exist
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        const firstError = '{{ $errors->first() }}';
        showToast(firstError, 'error');
    });
@endif

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        document.getElementById('loginForm').submit();
    }
    
    // Escape to clear form
    if (e.key === 'Escape') {
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
        showToast('Form cleared', 'info');
    }
});

// Demo account auto-fill
function fillDemoAccount(role) {
    if (role === 'admin') {
        document.getElementById('email').value = 'admin@example.com';
        document.getElementById('password').value = 'password';
    } else if (role === 'oracle') {
        document.getElementById('email').value = 'oracle@example.com';
        document.getElementById('password').value = 'password';
    }
    showToast('Demo account filled. Click Sign In to continue.', 'info');
}

// Initialize theme if needed
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference
    if (localStorage.getItem('theme') === 'dark' || 
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    }
});
</script>

<style>
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Smooth transitions */
input, button {
    transition: all 0.2s ease-in-out;
}

/* Focus styles */
input:focus, button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Custom checkbox */
input[type="checkbox"]:checked + div svg {
    display: block;
}

/* Gradient background animation */
.bg-gradient-to-br {
    background-size: 200% 200%;
    animation: gradientShift 10s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Shake animation for errors */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.border-red-500 {
    animation: shake 0.5s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .max-w-md {
        max-width: 100%;
    }
    .p-8 {
        padding: 1.5rem;
    }
}
</style>
@endsection