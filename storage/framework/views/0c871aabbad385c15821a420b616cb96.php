<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Log in to Music Town and continue streaming music.">
    <title>Login - Music Town</title>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <main class="auth-page">
        <a class="brand auth-brand" href="<?php echo e(url('/')); ?>" aria-label="Music Town home">
            <?php echo $__env->make('partials.brand-mark', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <span>Music Town</span>
        </a>

        <section class="auth-shell">
            <div class="auth-copy">
                <p class="eyebrow">Welcome back</p>
                <h1>Welcome back to Music Town.</h1>
                <p>Log in to continue listening, tracking rewards, and managing your Music Town account.</p>

                <div class="mini-player" aria-label="Featured mix">
                    <div class="disc-art">
                        <span></span>
                    </div>
                    <div>
                        <small>Music Town Mix</small>
                        <strong>Daily Listening Rewards</strong>
                    </div>
                    <div class="wave-bars" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>

            <form class="auth-card" method="POST" action="<?php echo e(route('login.submit')); ?>">
                <?php echo csrf_field(); ?>
                <div class="auth-card-heading">
                    <p class="eyebrow">Login</p>
                    <h2>Access your account</h2>
                </div>

                <?php if(session('status')): ?>
                    <p class="form-message success-message"><?php echo e(session('status')); ?></p>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="form-message error-message">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p><?php echo e($error); ?></p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <label>
                    Email address
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="you@example.com" autocomplete="email" required>
                </label>

                <label>
                    Password
                    <span class="password-field">
                        <input type="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" data-password-toggle>
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a16.6 16.6 0 0 1-2.2 3.1"></path>
                                <path d="M6.1 6.7C3.7 8.4 2.5 12 2.5 12s3.5 7 9.5 7a9.8 9.8 0 0 0 4.1-.9"></path>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                            </svg>
                        </button>
                    </span>
                </label>

                <div class="form-row">
                    <label class="check-field">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button class="button auth-submit" type="submit">Login</button>

                <p class="auth-switch">
                    New to Music Town?
                    <a href="<?php echo e(route('signup', request()->query())); ?>">Create account</a>
                </p>
            </form>
        </section>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\music\resources\views/auth/login.blade.php ENDPATH**/ ?>