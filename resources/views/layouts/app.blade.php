<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IdealTech - @yield('title')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-500: #0077ffff;
            --primary-400: #288cffff;
            --primary-300: #4da0ffff;
            --primary-200: #88c0ffff;
            --primary-100: #b7d9ffff;
            --primary-50: rgba(255, 208, 163, 0);
            
            --gray-900: #111827;
            --gray-800: #1F2937;
            --gray-700: #374151;
            --gray-600: #4B5563;
            --gray-500: #6B7280;
            --gray-400: #9CA3AF;
            --gray-300: #D1D5DB;
            --gray-200: #E5E7EB;
            --gray-100: #F3F4F6;
            --gray-50: #F9FAFB;
            
            --success-500: #10B981;
            --warning-500: #F59E0B;
            --danger-500: #EF4444;
            --info-500: #3B82F6;
            
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --header-height: 70px;
            
            --transition-base: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        [data-bs-theme="dark"] {
            --primary-500: #e46e00ff;
            --primary-400: #ff9735ff;
            --primary-300: #ffc085ff;
            
            --gray-900: #F9FAFB;
            --gray-800: #F3F4F6;
            --gray-700: #E5E7EB;
            --gray-600: #D1D5DB;
            --gray-500: #9CA3AF;
            --gray-400: #6B7280;
            --gray-300: #4B5563;
            --gray-200: #374151;
            --gray-100: #1F2937;
            --gray-50: #2b2b2b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-50);
            color: var(--gray-900);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        .app-header {
            height: var(--header-height);
            background-color: var(--gray-50);
            box-shadow: var(--shadow-sm);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            transition: var(--transition-base);
            backdrop-filter: blur(10px);
        }
        
        .header-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--gray-900);
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .brand-logo img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
        
        .brand-text {
            transition: var(--transition-base);
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-400) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Tabela sem cor de fundo */
        .table {
            background-color: transparent !important;
            margin-bottom: 0;
        }
        
        .table > :not(caption) > * > * {
            background-color: transparent !important;
        }
        
        /* Sidebar Styles */
        .app-sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            position: fixed;
            top: var(--header-height);
            left: 0;
            background-color: var(--gray-50);
            border-right: 1px solid var(--gray-200);
            transition: var(--transition-base);
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1020;
            padding: 1.5rem 0;
            box-shadow: var(--shadow-md);
        }
        
        .sidebar-collapsed .app-sidebar {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar-collapsed .brand-text,
        .sidebar-collapsed .nav-link-text,
        .sidebar-collapsed .nav-section {
            opacity: 0;
            visibility: hidden;
            width: 0;
            height: 0;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-section {
            padding: 0 1.25rem;
            margin: 1.5rem 0 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--gray-500);
            letter-spacing: 0.05em;
            transition: var(--transition-base);
        }
        
        .nav-item {
            margin-bottom: 0.25rem;
            padding: 0 0.75rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition-base);
            position: relative;
        }
        
        .nav-link:hover {
            background-color: var(--primary-50);
            color: var(--primary-500);
            transform: translateX(4p
            x);
        }
        
        .nav-link.active {
            background-color: var(--primary-50);
            color: var(--primary-500);
            font-weight: 600;
            box-shadow: 0 0 0 2.6px var(--primary-400);
        }
        
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--primary-500);
            border-radius: 0 4px 4px 0;
        }
        
        .nav-link i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
            transition: var(--transition-base);
        }
        
        .nav-link-text {
            transition: var(--transition-base);
        }
        
        /* Main Content */
        .app-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
            transition: var(--transition-base);
            min-height: calc(100vh - var(--header-height));
            background-color: var(--gray-50);
        }
        
        .sidebar-collapsed .app-main {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .page-title-section {
            flex: 1;
        }
        
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0 0 0.5rem;
            line-height: 1.2;
        }
        
        .page-subtitle {
            font-size: 1rem;
            color: var(--gray-600);
            margin: 0;
            font-weight: 400;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .breadcrumb-item a {
            color: var(--gray-600);
            text-decoration: none;
            transition: var(--transition-base);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary-500);
        }
        
        .breadcrumb-item.active {
            color: var(--primary-500);
            font-weight: 500;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--gray-400);
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            background-color: var(--gray-50);
            margin-bottom: 1.5rem;
            transition: var(--transition-base);
            position: relative;
            overflow: hidden;
            border: 1px solid var(--gray-200);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 16px 16px 0 0 !important;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            transition: var(--transition-base);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-400) 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }
        
        .btn-outline-primary {
            border: 1px solid var(--primary-500);
            color: var(--primary-500);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-500);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--gray-600);
            font-size: 1.25rem;
            cursor: pointer;
            transition: var(--transition-base);
            padding: 0.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-toggle:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }
        
        /* User Dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 10px;
            transition: var(--transition-base);
        }
        
        .user-dropdown:hover {
            background-color: var(--gray-100);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-400) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 0.875rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        
        /* Theme Toggle */
        .theme-toggle {
            background: none;
            border: none;
            color: var(--gray-600);
            font-size: 1.25rem;
            cursor: pointer;
            transition: var(--transition-base);
            padding: 0.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .theme-toggle:hover {
            background-color: var(--gray-100);
            color: var(--gray-900);
        }
        
        /* Alerts */
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow-sm);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert i {
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-500);
            border-left: 4px solid var(--success-500);
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-500);
            border-left: 4px solid var(--danger-500);
        }
        
        /* Status Badges */
        .badge {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
        }
        
        .badge-success {
            background-color: var(--success-500);
        }
        
        .badge-warning {
            background-color: var(--warning-500);
        }
        
        .badge-danger {
            background-color: var(--danger-500);
        }
        
        /* Paginação padrão */
        .pagination .page-link {
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
            background-color: transparent;
        }
        
        .pagination .page-link:hover {
            background-color: var(--gray-100);
            border-color: var(--gray-400);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-500);
            border-color: var(--primary-500);
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--gray-500);
            background-color: transparent;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar-mobile-show .app-sidebar {
                transform: translateX(0);
            }
            
            .app-main {
                margin-left: 0;
            }
            
            .sidebar-collapsed .app-main {
                margin-left: 0;
            }
            
            .user-info {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .app-main {
                padding: 1.5rem 1rem;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        .status-pagamento {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pago {
            background-color: var(--success-500);
            color: white;
        }

        .status-pendente {
            background-color: var(--warning-500);
            color: #212529;
        }

        .status-nao-pago {
            background-color: var(--danger-500);
            color: white;
        }

        /* Versões outline para tabelas */
        .status-pagamento-outline {
            border-radius: 6px;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: transparent;
            border: 2px solid;
        }

        .status-pago-outline {
            border-color: var(--success-500);
            color: var(--success-500);
        }

        .status-pendente-outline {
            border-color: var(--warning-500);
            color: var(--warning-500);
        }

        .status-nao-pago-outline {
            border-color: var(--danger-500);
            color: var(--danger-500);
        }

        /* Versões pequenas para tabelas */
        .status-pagamento-sm {
            padding: 0.2rem 0.5rem;
            font-size: 0.65rem;
        }

        /* Cards de resumo financeiro */
        .card-pago {
            border-left: 4px solid var(--success-500);
        }

        .card-pendente {
            border-left: 4px solid var(--warning-500);
        }

        .card-nao-pago {
            border-left: 4px solid var(--danger-500);
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="app-header">
        <div class="d-flex align-items-center">
            <!-- Mobile Sidebar Toggle -->
            <button class="sidebar-toggle me-3 d-lg-none">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="header-brand me-4">
                <div class="brand-logo">
                    <img src="{{ asset('storage/img/logo2.png') }}" alt="IdealTech">
                </div>
            </a>
            
            <!-- Desktop Sidebar Toggle -->
            <button class="sidebar-toggle me-3 d-none d-lg-block">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <div class="d-flex align-items-center ms-auto">
            <!-- Theme Toggle -->
            <button class="theme-toggle me-2">
                <i class="fas fa-moon"></i>
            </button>
            
            @auth
            <!-- User Dropdown -->
            <div class="dropdown">
                <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info d-none d-lg-block">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">
                            @if(Auth::user()->is_admin)
                                Administrador
                            @else
                                Usuário
                            @endif
                        </div>
                    </div>
                    <i class="fas fa-chevron-down ms-1 d-none d-lg-block" style="font-size: 0.875rem;"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; min-width: 200px;">
                    <li class="px-3 py-2 border-bottom">
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role text-muted small">
                            @if(Auth::user()->is_admin)
                                Administrador
                            @else
                                Usuário
                            @endif
                        </div>
                    </li>
                    @if(Auth::user()->is_admin)
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-cog fa-fw"></i> Painel Admin
                        </a>
                    </li>
                    @endif
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="#">
                            <i class="fas fa-user fa-fw"></i> Meu Perfil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fa-fw"></i> Sair
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
            @endauth
            
            @guest
            <a class="btn btn-outline-primary" href="{{ route('login') }}">Login</a>
            @endguest
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="app-sidebar">
        <ul class="sidebar-nav">
            <li class="nav-section">PRINCIPAL</li>
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section">GERENCIAMENTO</li>
            <li class="nav-item">
                <a href="{{ route('clientes.index') }}" class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="nav-link-text">Clientes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('servicos.index') }}" class="nav-link {{ Request::is('servicos*') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span class="nav-link-text">Serviços</span>
                </a>
            </li>
            
            @auth
                @if(Auth::user()->is_admin)
                <li class="nav-section">ADMINISTRAÇÃO</li>
                <li class="nav-item">
                    <a href="{{ route('admin.usuarios.index') }}" class="nav-link {{ Request::is('admin/usuarios*') ? 'active' : '' }}">
                        <i class="fas fa-user-cog"></i>
                        <span class="nav-link-text">Usuários</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.relatorios') }}" class="nav-link {{ Request::is('admin/relatorios*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span class="nav-link-text">Relatórios</span>
                    </a>
                </li>
                @endif
            @endauth
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="app-main">
        <div class="container-fluid">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    @endif
                </ol>
            </nav>
            
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="page-title-section">
                    <h1 class="page-title">@yield('title')</h1>
                    @hasSection('subtitle')
                        <p class="page-subtitle">@yield('subtitle')</p>
                    @endif
                </div>
                <div>
                    @yield('header-actions')
                </div>
            </div>
            
            <!-- Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div class="flex-grow-1">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="flex-grow-1">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="flex-grow-1">
                        <strong>Erro!</strong> Por favor, verifique os campos do formulário.
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Content -->
            <div class="fade-in">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $('.sidebar-toggle').click(function() {
                $('body').toggleClass('sidebar-collapsed');
                
                // Change icon
                const icon = $(this).find('i');
                if ($('body').hasClass('sidebar-collapsed')) {
                    icon.removeClass('fa-chevron-left').addClass('fa-chevron-right');
                } else {
                    icon.removeClass('fa-chevron-right').addClass('fa-chevron-left');
                }
                
                // Save state
                localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed'));
            });
            
            // Mobile sidebar toggle
            $('.sidebar-toggle.me-3.d-lg-none').click(function() {
                $('body').toggleClass('sidebar-mobile-show');
            });
            
            // Check initial sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                $('body').addClass('sidebar-collapsed');
                $('.sidebar-toggle i').removeClass('fa-chevron-left').addClass('fa-chevron-right');
            }
            
            // Theme toggle
            $('.theme-toggle').click(function() {
                const html = $('html');
                const isDark = html.attr('data-bs-theme') === 'dark';
                
                // Toggle theme
                html.attr('data-bs-theme', isDark ? 'light' : 'dark');
                
                // Change icon
                $(this).find('i').toggleClass('fa-moon fa-sun');
                
                // Save preference
                localStorage.setItem('darkMode', !isDark);
            });
            
            // Check theme preference
            if (localStorage.getItem('darkMode') === 'true') {
                $('html').attr('data-bs-theme', 'dark');
                $('.theme-toggle i').removeClass('fa-moon').addClass('fa-sun');
            }
            
            // Check system preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && 
                !localStorage.getItem('darkMode')) {
                $('html').attr('data-bs-theme', 'dark');
                $('.theme-toggle i').removeClass('fa-moon').addClass('fa-sun');
                localStorage.setItem('darkMode', 'true');
            }
            
            // Close mobile sidebar when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('.app-sidebar').length && 
                    !$(event.target).closest('.sidebar-toggle.me-3.d-lg-none').length && 
                    $('body').hasClass('sidebar-mobile-show')) {
                    $('body').removeClass('sidebar-mobile-show');
                }
            });

            // Auto-dismiss alerts after 5 seconds
            $('.alert').delay(5000).fadeOut(400);
        });
    </script>
    
    @stack('scripts')
</body>
</html>