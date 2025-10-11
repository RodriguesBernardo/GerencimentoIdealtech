<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - IdealTech</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --idealtech-blue: #ff9100ff;
            --idealtech-blue-dark: #ff9100ff;
            --idealtech-blue-light: #ff9100ff;
            --primary-color: #ff9100ff;
            --primary-light: #ff9100ff;
            --secondary-color: #087c04;
            --dark-color: #2b2b2b;
            --dark-light: #3d3d3d;
            --light-color: #f8f9fa;
            --text-color: #2b2b2b;
            --card-bg: #ffffff;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        
        [data-bs-theme="dark"] {
            --dark-color: #f8f9fa;
            --dark-light: #e0e0e0;
            --light-color: #2b2b2b;
            --text-color: #f8f9fa;
            --card-bg: #2b2b2b;
        }
        
        body {
            background-color: var(--light-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #1e2be5ff 0%, #1e88e5 100%);
            background-size: cover;
            background-position: center;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 480px;
            border-top: 4px solid var(--idealtech-blue);
        }

        .auth-logo {
            max-width: 120px;
            height: auto;
            margin: 0 auto 15px auto;
        }

        .auth-logo img {
            display: block;
            width: 150%;
            margin-left: -30px;
            height: auto;
            object-fit: contain;
        }

        .auth-title {
            color: var(--text-color);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            color: var(--dark-light);
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        .auth-form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-text {
            background-color: rgba(9, 2, 89, 0.1);
            border: none;
            color: var(--primary-color);
            padding: 0 15px;
            height: 100%;
            position: absolute;
            z-index: 10;
        }

        .form-control {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: var(--light-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(9, 2, 89, 0.2);
        }

        .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 0;
            background: transparent;
            border: none;
            color: var(--dark-light);
            z-index: 10;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 0;
            margin-right: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--dark-light);
            font-size: 0.9rem;
        }

        .forgot-password {
            color: var(--primary-color);
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .auth-status {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 8px;
            background-color: rgba(40, 199, 111, 0.1);
            color: var(--success-color);
            font-size: 0.9rem;
        }

        .form-error {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 6px;
        }

        .auth-footer-text {
            color: var(--dark-light);
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        .auth-footer-link {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-footer-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        .btn-idealtech-blue {
            background-color: var(--idealtech-blue);
            color: white;
            border: none;
            transition: all 0.3s ease;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-idealtech-blue:hover {
            background-color: var(--idealtech-blue-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(9, 2, 89, 0.3);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 30px 20px;
            }
            
            .auth-logo {
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="text-center mb-5">
                <div class="auth-logo">
                    <img src="{{ asset('storage/img/logo2.png') }}" alt="IdealTech">
                </div>
                <h2 class="auth-title">IdealTech Soluções em Informática</h2>
            </div>


            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" 
                               required autofocus autocomplete="username" class="form-control" />
                    </div>
                    @if($errors->has('email'))
                        <div class="form-error">
                            {{ $errors->first('email') }}
                        </div>
                    @endif
                </div>

                <!-- Password -->
                <div class="form-group mt-4">
                    <label for="password" class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" name="password" 
                               required autocomplete="current-password" class="form-control" />
                        <button type="button" class="input-group-text toggle-password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <div class="form-error">
                            {{ $errors->first('password') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="form-check">
                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                        <label for="remember_me" class="form-check-label">Lembrar-me</label>
                    </div>
                    
                    @if (Route::has('password.request'))
                        <a class="forgot-password" href="{{ route('password.request') }}">
                            Esqueceu sua senha?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-idealtech-blue btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i> Entrar
                    </button>
                </div>

                <!-- Footer Text -->
                <div class="text-center mt-4">
                    <p class="auth-footer-text">
                        Sistema de Gestão IdealTech
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.querySelector('.toggle-password');
            const password = document.querySelector('#password');
            
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function() {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                });
            }

            // Theme toggle functionality
            const themeToggle = document.createElement('button');
            themeToggle.className = 'theme-toggle btn btn-sm position-fixed bottom-0 end-0 m-3';
            themeToggle.onclick = function() {
                const html = document.documentElement;
                const isDark = html.getAttribute('data-bs-theme') === 'dark';
                html.setAttribute('data-bs-theme', isDark ? 'light' : 'dark');
                localStorage.setItem('darkMode', !isDark);
            };
            
            // Check for saved theme preference
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
            
            document.body.appendChild(themeToggle);
        });
    </script>
</body>
</html>