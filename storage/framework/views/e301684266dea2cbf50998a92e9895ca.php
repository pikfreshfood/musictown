<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Music Town</title>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }
        .admin-login-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 12px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(59,130,246,0.12);
            padding: clamp(28px, 4vw, 44px);
            width: 100%;
            max-width: 420px;
        }
        .admin-login-card h1 {
            font-size: 1.6rem;
            margin: 0 0 4px;
        }
        .admin-login-card p {
            color: var(--muted);
            margin: 0 0 24px;
            font-size: 0.9rem;
        }
        .admin-login-brand {
            align-items: center;
            display: inline-flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 14px;
        }
        .admin-login-brand span:last-child {
            background: linear-gradient(135deg, var(--blue), var(--blue-soft));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: 800;
        }
        .admin-login-card label {
            color: #dce7f8;
            display: grid;
            font-size: 0.9rem;
            font-weight: 800;
            gap: 9px;
            margin-bottom: 16px;
        }
        .admin-login-card input {
            background: rgba(2, 6, 14, 0.82);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            color: white;
            min-height: 52px;
            outline: 0;
            padding: 0 16px;
            width: 100%;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .admin-login-card input:focus {
            border-color: rgba(59, 130, 246, 0.74);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.16);
        }
        .error-message {
            background: rgba(255, 50, 50, 0.12);
            border: 1px solid rgba(255, 50, 50, 0.4);
            color: #ff6b6b;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div style="text-align:center;margin-bottom:24px;">
            <div class="admin-login-brand">
                <?php echo $__env->make('partials.brand-mark', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span>Music Town</span>
            </div>
            <h1>Admin Login</h1>
            <p>Sign in to manage the platform</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="error-message">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p style="margin:0;"><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.login.submit')); ?>">
            <?php echo csrf_field(); ?>
            <label>
                Username
                <input type="text" name="username" placeholder="admin" required>
            </label>
            <label>
                Password
                <input type="password" name="password" placeholder="••••••" required>
            </label>
            <button class="button auth-submit" type="submit" style="border:0;cursor:pointer;width:100%;">Login</button>
        </form>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\music\resources\views/admin/login.blade.php ENDPATH**/ ?>