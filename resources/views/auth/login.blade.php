@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-md">
    <!-- Logo & App Name -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-tshirt text-2xl text-purple-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">LaundryKu</h1>
        <p class="text-purple-200 mt-1">Admin Dashboard</p>
    </div>

    <!-- Login Form -->
    <div class="bg-white rounded-2xl shadow-xl p-6">
        <h2 class="text-xl font-semibold text-gray-800 text-center mb-6">Masuk ke Akun Anda</h2>
        
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
            <!-- Email Input -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-gray-400"></i>Alamat Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    required 
                    autocomplete="email"
                    autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 input-autofill"
                    placeholder="email@example.com"
                    inputmode="email"
                >
            </div>

            <!-- Password Input -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-gray-400"></i>Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 input-autofill pr-12"
                        placeholder="Masukkan password"
                    >
                    <button 
                        type="button" 
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePassword()"
                    >
                        <i class="fas fa-eye" id="passwordToggle"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember"
                        class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                    >
                    <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                </label>
                
                <a href="{{ route('password.request') }}" class="text-sm text-purple-600 hover:text-purple-500">
                    Lupa password?
                </a>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 btn-touch"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
            </button>

            <!-- Biometric Login (Conditional) -->
            <div id="biometricSection" class="hidden mt-4">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">atau masuk dengan</span>
                    </div>
                </div>
                
                <button 
                    type="button"
                    id="biometricBtn"
                    class="w-full mt-4 bg-white border border-gray-300 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-50 transition-all duration-200 btn-touch"
                >
                    <i class="fas fa-fingerprint mr-2"></i>
                    <span id="biometricText">Touch ID / Face ID</span>
                </button>
            </div>
        </form>

        <!-- Demo Account Info (for development) -->
        @if(app()->environment('local'))
        <div class="mt-6 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
            <p class="text-xs text-yellow-800 text-center">
                <strong>Demo Account:</strong><br>
                Email: admin@laundryku.com<br>
                Password: password
            </p>
        </div>
        @endif
    </div>

    <!-- App Version -->
    <p class="text-center text-purple-200 text-xs mt-6">
        LaundryKu v1.0 &copy; {{ date('Y') }}
    </p>
</div>

@push('scripts')
<script>
    // Toggle Password Visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordToggle.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordToggle.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Biometric Authentication Support Detection
    document.addEventListener('DOMContentLoaded', function() {
        const biometricSection = document.getElementById('biometricSection');
        const biometricBtn = document.getElementById('biometricBtn');
        const biometricText = document.getElementById('biometricText');
        
        // Check if WebAuthn is supported
        if (window.PublicKeyCredential) {
            // Check specific biometric availability
            PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable()
                .then(available => {
                    if (available) {
                        biometricSection.classList.remove('hidden');
                        
                        // Detect device type for better text
                        if (navigator.userAgent.match(/iPhone|iPad|iPod/)) {
                            biometricText.textContent = 'Face ID';
                        } else if (navigator.userAgent.match(/Android/)) {
                            biometricText.textContent = 'Fingerprint';
                        } else {
                            biometricText.textContent = 'Biometric';
                        }
                    }
                })
                .catch(error => {
                    console.log('Biometric not available:', error);
                });
        }

        // Biometric Login Handler
        biometricBtn.addEventListener('click', function() {
            // Simulate biometric login for demo
            simulateBiometricLogin();
        });

        // Auto-fill optimization
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            // Add loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            submitBtn.disabled = true;
        });

        // Enter key to submit
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                loginForm.requestSubmit();
            }
        });
    });

    // Simulate Biometric Login (for demo)
    function simulateBiometricLogin() {
        const biometricBtn = document.getElementById('biometricBtn');
        const originalText = biometricBtn.innerHTML;
        
        biometricBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memverifikasi...';
        biometricBtn.disabled = true;
        
        setTimeout(() => {
            // Auto-fill demo credentials
            document.getElementById('email').value = 'admin@laundryku.com';
            document.getElementById('password').value = 'password';
            
            biometricBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Berhasil!';
            biometricBtn.classList.remove('bg-white', 'border-gray-300', 'text-gray-700');
            biometricBtn.classList.add('bg-green-500', 'text-white');
            
            setTimeout(() => {
                // Auto-submit after successful biometric
                document.getElementById('loginForm').submit();
            }, 1000);
        }, 1500);
    }

    // Enhanced auto-fill detection
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('animationstart', (e) => {
            if (e.animationName === 'onAutoFillStart') {
                input.parentElement.classList.add('auto-filled');
            }
        });
    });
</script>
@endpush
@endsection