<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - Music Town</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(59,130,246,0.2); border-radius: 3px; }

        .admin-sidebar {
            width: 250px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0a1428, #02040a);
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 30;
        }
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(59,130,246,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 800;
        }
        .sidebar-brand .brand-mark {
            align-items: center;
            background: linear-gradient(135deg, var(--blue), var(--blue-soft));
            box-shadow: 0 0 24px var(--glow-blue), 0 0 34px var(--glow-blue);
            border-radius: 50%;
            color: white;
            display: inline-flex;
            height: 36px;
            justify-content: center;
            width: 36px;
            font-size: 0.9rem;
        }
        .sidebar-nav {
            flex: 1;
            padding: 12px 10px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .sidebar-nav .nav-label {
            color: var(--muted);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 16px 12px 6px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #b0c4de;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 180ms ease, color 180ms ease;
        }
        .sidebar-nav a:hover {
            background: rgba(59,130,246,0.1);
            color: var(--ink);
        }
        .sidebar-nav a.active {
            background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(59,130,246,0.1));
            color: white;
        }
        .sidebar-nav a .icon {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .sidebar-nav a .badge {
            margin-left: auto;
            background: var(--blue);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid rgba(59,130,246,0.1);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #b0c4de;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 180ms ease, color 180ms ease;
        }
        .sidebar-footer a:hover {
            background: rgba(255,50,50,0.1);
            color: #ff6b6b;
        }

        .admin-main {
            margin-left: 250px;
            flex: 1;
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 8%, rgba(59,130,246,0.12), transparent 28rem),
                radial-gradient(circle at 85% 20%, rgba(147,197,253,0.1), transparent 24rem),
                linear-gradient(180deg, #030711 0%, #0a1428 48%, #02040a 100%);
        }
        .admin-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 32px;
            border-bottom: 1px solid var(--line);
            background: rgba(3,7,17,0.6);
            backdrop-filter: blur(12px);
        }
        .admin-topbar h2 {
            margin: 0;
            font-size: 1.2rem;
        }
        .admin-topbar .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--gold);
        }
        .admin-content {
            padding: 32px;
            max-width: 1200px;
        }
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .success-message {
            background: rgba(59,130,246,0.12);
            border: 1px solid rgba(59,130,246,0.4);
            color: #60a5fa;
        }
        .error-message {
            background: rgba(255,50,50,0.12);
            border: 1px solid rgba(255,50,50,0.4);
            color: #ff6b6b;
        }
        .admin-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 24px 28px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 20px 24px;
        }
        .stat-card .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--gold);
            margin: 4px 0 0;
        }
        .stat-card .stat-label {
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity 180ms ease, transform 180ms ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: linear-gradient(135deg, var(--blue), var(--blue-soft) 48%, #1d4ed8);
            color: white;
        }
        .btn-danger {
            background: rgba(255,50,50,0.2);
            border: 1px solid rgba(255,50,50,0.35);
            color: #ff6b6b;
        }
        .btn-danger:hover {
            background: rgba(255,50,50,0.3);
        }
        .btn-ghost {
            background: rgba(59,130,246,0.08);
            border: 1px solid var(--line);
            color: #dce7f8;
        }
        .btn-ghost:hover {
            background: rgba(59,130,246,0.15);
        }
        .btn-green {
            background: linear-gradient(135deg, #2a8f4a, #1e7a3a);
            color: white;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        .table-wrap {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid var(--line);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
        }
        table th {
            padding: 14px 18px;
            text-align: left;
            font-size: 0.75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 800;
            border-bottom: 1px solid var(--line);
        }
        table td {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(72,181,255,0.06);
            font-size: 0.9rem;
        }
        table tr:last-child td { border-bottom: none; }
        table tr:hover td { background: rgba(59,130,246,0.04); }
        .input-field {
            background: rgba(2,6,14,0.82);
            border: 1px solid rgba(59,130,246,0.25);
            border-radius: 8px;
            color: white;
            min-height: 46px;
            padding: 0 14px;
            outline: 0;
            width: 100%;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .input-field:focus {
            border-color: rgba(59,130,246,0.74);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.16);
        }
        label.form-label {
            display: grid;
            gap: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #dce7f8;
        }
        .pagination-wrap nav[role="navigation"] > div:first-child { display: none; }
        .pagination-wrap nav[role="navigation"] > div:last-child {
            display: flex; gap: 6px; flex-wrap: wrap; margin-top: 16px;
        }
        .pagination-wrap nav[role="navigation"] a,
        .pagination-wrap nav[role="navigation"] span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 8px;
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line); border-radius: 6px;
            color: #dce7f8; font-size: 0.85rem; font-weight: 700;
            text-decoration: none;
        }
        .pagination-wrap nav[role="navigation"] a:hover { border-color: rgba(59,130,246,0.6); color: var(--blue-soft); }
        .pagination-wrap nav[role="navigation"] span[aria-current="page"] {
            background: linear-gradient(135deg, var(--blue), var(--blue-soft));
            border-color: transparent; color: white;
        }
        .sidebar-toggle {
            display: none;
            background: rgba(59,130,246,0.1);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #dce7f8;
            cursor: pointer;
            padding: 8px 10px;
            line-height: 1;
            transition: background 180ms ease;
        }
        .sidebar-toggle:hover { background: rgba(59,130,246,0.2); }
        .sidebar-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            margin: 4px 0;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 29;
        }
        .sidebar-overlay.is-open { display: block; }

        @media (max-width: 860px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 240ms ease;
                width: 260px;
            }
            .admin-sidebar.is-open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .admin-content { padding: 20px; }
            .admin-topbar { padding: 14px 20px; }
            .sidebar-toggle { display: inline-flex; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-brand">
            @include('partials.brand-mark')
            <span>Music Town</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="icon">&#9632;</span> Dashboard
            </a>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <span class="icon">&#9644;</span> Users
            </a>
            <a href="{{ route('admin.music') }}" class="{{ request()->routeIs('admin.music') ? 'active' : '' }}">
                <span class="icon">&#9835;</span> Music
            </a>
            <div class="nav-label">Finance</div>
            <a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'active' : '' }}">
                <span class="icon">&#9679;</span> Premium Payments
            </a>
            <a href="{{ route('admin.ncoin-payments') }}" class="{{ request()->routeIs('admin.ncoin-payments') ? 'active' : '' }}">
                <span class="icon">&#9733;</span> Ncoin Payments
            </a>
            <a href="{{ route('admin.ncoin-codes') }}" class="{{ request()->routeIs('admin.ncoin-codes') ? 'active' : '' }}">
                <span class="icon">&#9733;</span> Ncoin Codes
            </a>
            <a href="{{ route('admin.payment-account') }}" class="{{ request()->routeIs('admin.payment-account') ? 'active' : '' }}">
                <span class="icon">&#9671;</span> Payment Account
            </a>
            <div class="nav-label">Administration</div>
            <a href="{{ route('admin.sub-admins') }}" class="{{ request()->routeIs('admin.sub-admins') ? 'active' : '' }}">
                <span class="icon">&#9737;</span> Sub Admins
            </a>
            <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <span class="icon">&#9881;</span> Settings
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('layout-logout').submit();">
                <span class="icon">&#8617;</span> Logout
            </a>
            <form id="layout-logout" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <h2>@yield('page-title', 'Dashboard')</h2>
            </div>
            <div class="admin-user">
                <span>{{ Auth::user()->name }}</span>
                <small style="color:var(--muted);font-weight:600;font-size:0.75rem;text-transform:uppercase;">{{ Auth::user()->role ?? 'admin' }}</small>
            </div>
        </div>
        <div class="admin-content">
            @if (session('success'))
                <p class="form-message success-message">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p class="form-message error-message">{{ session('error') }}</p>
            @endif
            @if ($errors->any())
                <div class="form-message error-message">
                    @foreach ($errors->all() as $error)
                        <p style="margin:0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        (function() {
            var sidebar = document.getElementById('admin-sidebar');
            var toggle = document.getElementById('sidebar-toggle');
            var overlay = document.getElementById('sidebar-overlay');

            function openSidebar() {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-open');
            }

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
            }

            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar.classList.contains('is-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            // Close sidebar when clicking a nav link on mobile
            sidebar.querySelectorAll('.sidebar-nav a, .sidebar-footer a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 860) {
                        closeSidebar();
                    }
                });
            });
        })();
    </script>
</body>
</html>
