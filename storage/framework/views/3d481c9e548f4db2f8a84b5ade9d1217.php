<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $__env->yieldContent('meta-description', 'Music Town profile'); ?>">
    <title><?php echo $__env->yieldContent('title', 'Profile'); ?> - Music Town</title>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(59,130,246,0.2); border-radius: 3px; }

        .user-sidebar {
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
            transition: transform 240ms ease, width 240ms ease;
        }
        .user-sidebar.collapsed {
            width: 0;
            overflow: hidden;
        }
        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(59,130,246,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 800;
            white-space: nowrap;
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
            flex-shrink: 0;
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
            white-space: nowrap;
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
            background: rgba(220,38,38,0.1);
            color: #f87171;
        }

        .user-main {
            margin-left: 250px;
            flex: 1;
            min-height: 100vh;
            transition: margin-left 240ms ease;
            background:
                radial-gradient(circle at 15% 8%, rgba(59,130,246,0.12), transparent 28rem),
                radial-gradient(circle at 85% 20%, rgba(147,197,253,0.1), transparent 24rem),
                linear-gradient(180deg, #030711 0%, #0a1428 48%, #02040a 100%);
        }
        .user-main.expanded {
            margin-left: 0;
        }
        .user-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 32px;
            border-bottom: 1px solid var(--line);
            background: rgba(3,7,17,0.6);
            backdrop-filter: blur(12px);
        }
        .user-topbar .sidebar-toggle {
            display: inline-flex;
            align-items: center;
            background: rgba(59,130,246,0.1);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #dce7f8;
            cursor: pointer;
            padding: 8px 10px;
            line-height: 1;
            transition: background 180ms ease;
        }
        .user-topbar .sidebar-toggle:hover {
            background: rgba(59,130,246,0.2);
        }
        .user-topbar .sidebar-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            margin: 4px 0;
        }
        .user-topbar h2 {
            margin: 0;
            font-size: 1rem;
        }
        .user-topbar .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--blue-soft);
        }
        .user-content {
            padding: 32px;
            max-width: 1200px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 29;
        }
        .sidebar-overlay.is-open { display: block; }

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
            background: rgba(220,38,38,0.12);
            border: 1px solid rgba(220,38,38,0.4);
            color: #f87171;
        }

        @media (max-width: 860px) {
            .user-sidebar {
                transform: translateX(-100%);
                width: 260px !important;
            }
            .user-sidebar.is-open {
                transform: translateX(0);
            }
            .user-sidebar.collapsed {
                width: 260px !important;
            }
            .user-main {
                margin-left: 0 !important;
            }
            .user-content {
                padding: 20px;
            }
            .user-topbar {
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside class="user-sidebar" id="user-sidebar">
        <div class="sidebar-brand">
            <?php echo $__env->make('partials.brand-mark', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <span>Music Town</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Main</div>
            <a href="<?php echo e(route('profile')); ?>" class="<?php echo e(request()->routeIs('profile') && !request()->routeIs('profile.*') ? 'active' : ''); ?>">
                <span class="icon">&#9632;</span> Dashboard
            </a>
            <a href="<?php echo e(route('profile.withdrawal')); ?>" class="<?php echo e(request()->routeIs('profile.withdrawal') ? 'active' : ''); ?>">
                <span class="icon">&#8611;</span> Withdrawal
            </a>
            <a href="<?php echo e(route('profile.history')); ?>" class="<?php echo e(request()->routeIs('profile.history') ? 'active' : ''); ?>">
                <span class="icon">&#9776;</span> History
            </a>
            <a href="<?php echo e(route('profile.referrals')); ?>" class="<?php echo e(request()->routeIs('profile.referrals') ? 'active' : ''); ?>">
                <span class="icon">&#128101;</span> Referrals
            </a>
            <a href="<?php echo e(route('profile.upgrade.form')); ?>" class="<?php echo e(request()->routeIs('profile.upgrade.form') ? 'active' : ''); ?>">
                <span class="icon">&#11088;</span> Upgrade Account
            </a>
            <div class="nav-label">Account</div>
            <a href="<?php echo e(route('profile.settings')); ?>" class="<?php echo e(request()->routeIs('profile.settings') ? 'active' : ''); ?>">
                <span class="icon">&#9881;</span> Settings
            </a>
            <a href="<?php echo e(route('profile.password.form')); ?>" class="<?php echo e(request()->routeIs('profile.password.form') ? 'active' : ''); ?>">
                <span class="icon">&#128274;</span> Change Password
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('user-layout-logout').submit();">
                <span class="icon">&#8617;</span> Logout
            </a>
            <form id="user-layout-logout" method="POST" action="<?php echo e(route('logout')); ?>" style="display:none;"><?php echo csrf_field(); ?></form>
        </div>
    </aside>

    <main class="user-main" id="user-main">
        <div class="user-topbar">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
                    <span></span><span></span>
                </button>
                <h2><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></h2>
            </div>
            <div class="user-info">
                <span><?php echo e(Auth::user()->name); ?></span>
            </div>
        </div>
        <div class="user-content">
            <?php if(session('success')): ?>
                <p class="form-message success-message"><?php echo e(session('success')); ?></p>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <p class="form-message error-message"><?php echo e(session('error')); ?></p>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="form-message error-message">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p style="margin:0;"><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <script>
        (function() {
            var sidebar = document.getElementById('user-sidebar');
            var main = document.getElementById('user-main');
            var toggle = document.getElementById('sidebar-toggle');
            var overlay = document.getElementById('sidebar-overlay');
            var isCollapsed = false;

            function toggleSidebar() {
                if (window.innerWidth <= 860) {
                    sidebar.classList.toggle('is-open');
                    overlay.classList.toggle('is-open');
                } else {
                    isCollapsed = !isCollapsed;
                    sidebar.classList.toggle('collapsed');
                    main.classList.toggle('expanded');
                }
            }

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
            }

            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            sidebar.querySelectorAll('.sidebar-nav a, .sidebar-footer a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 860) {
                        closeSidebar();
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 860 && sidebar.classList.contains('is-open')) {
                    closeSidebar();
                }
            });
        })();
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\music\resources\views/layouts/user.blade.php ENDPATH**/ ?>