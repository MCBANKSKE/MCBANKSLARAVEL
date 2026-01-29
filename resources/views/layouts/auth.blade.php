<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Authentication')</title>
    
    <!-- Favicon -->
    <link href="{{ asset('favicon.ico') }}" rel="icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles for Auth Layout -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .form-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-input {
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .password-toggle {
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s;
        }
        
        .password-toggle:hover {
            color: #4b5563;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
            background-color: #e5e7eb;
        }
        
        .strength-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
 
    
    <!-- Livewire -->
    @livewireStyles
    
    @stack('styles')
</head>
<body class="gradient-bg flex items-center justify-center p-4">

    <!-- Main Content -->
    <main>
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize animations
            gsap.from('.auth-card', {
                duration: 0.8,
                y: 30,
                opacity: 0,
                ease: "power3.out"
            });

            // Input focus effects
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
                
                // Initialize if has value
                if (input.value) {
                    input.parentElement.classList.add('focused');
                }
            });

            // Password toggle
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Select styling
            document.querySelectorAll('.form-select').forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
                
                // Initialize
                if (select.value) select.classList.add('has-value');
            });

            // Checkbox styling
            document.querySelectorAll('.checkbox-input').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkmark = this.nextElementSibling.querySelector('.checkmark');
                    if (this.checked) {
                        checkmark.style.opacity = '1';
                        checkmark.style.transform = 'scale(1)';
                    } else {
                        checkmark.style.opacity = '0';
                        checkmark.style.transform = 'scale(0)';
                    }
                });
            });

            // Button loading states
            document.addEventListener('livewire:request', function() {
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                }
            });

            document.addEventListener('livewire:response', function() {
                const submitBtn = document.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.classList.remove('loading');
                }
            });
            // Password strength indicator
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const strengthBar = document.querySelector('.strength-bar');
                    const strengthText = document.querySelector('.strength-text');
                    const password = this.value;
                    let strength = 0;
                    
                    if (password.length >= 8) strength += 25;
                    if (/[A-Z]/.test(password)) strength += 25;
                    if (/[0-9]/.test(password)) strength += 25;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 25;
                    
                    if (strengthBar) {
                        strengthBar.style.width = strength + '%';
                        
                        if (strength < 50) {
                            strengthBar.style.backgroundColor = '#e53e3e';
                            if (strengthText) strengthText.textContent = 'Weak password';
                        } else if (strength < 75) {
                            strengthBar.style.backgroundColor = '#ed8936';
                            if (strengthText) strengthText.textContent = 'Fair password';
                        } else {
                            strengthBar.style.backgroundColor = '#48bb78';
                            if (strengthText) strengthText.textContent = 'Strong password';
                        }
                    }
                });
            }
        });
    </script>
    
    @livewireScripts
    @stack('scripts')
</body>
</html>