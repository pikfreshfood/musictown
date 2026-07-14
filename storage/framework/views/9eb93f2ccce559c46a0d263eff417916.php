<?php
    $songs = [
        ['title' => 'Jesus Iye', 'artist' => 'Nathaniel Bassey'],
        ['title' => 'Village Girl', 'artist' => 'Speed Darlington'],
        ['title' => 'Calculate', 'artist' => 'Kidd Carder'],
        ['title' => 'Odo', 'artist' => 'Mr P Ft Stonebwoy'],
        ['title' => 'Sweet Love', 'artist' => 'Burna Boy'],
        ['title' => 'Laho (Remix)', 'artist' => 'Shallipopi Ft. Burna Boy'],
    ];

    $faqs = [
        ['question' => 'How do I register on Music Town?', 'answer' => 'Joining Music Town is easy. Simply create an account with your email and start streaming.'],
        ['question' => 'Do I need a referral to get paid?', 'answer' => 'No, you do not need to refer anyone to get paid on Music Town. Referral rewards are a bonus.'],
        ['question' => 'How will I receive my streaming revenues?', 'answer' => 'Streaming revenues can be received via bank transfer directly to your Nigerian bank account.'],
        ['question' => 'How can I start streaming music?', 'answer' => 'You can start streaming music as soon as you register.'],
        ['question' => 'What tier plans do you offer?', 'answer' => 'We offer Tier 0 (free), Tier 1, Tier 2, and Tier 3 with increasing withdrawal limits.'],
        ['question' => 'What should I do if I am having trouble streaming?', 'answer' => 'If you encounter streaming issues, contact our customer care team for a swift resolution.'],
        ['question' => 'How can I withdraw my earnings?', 'answer' => 'You can withdraw your earnings via bank transfer once your balance reaches a minimum of ₦10,000.'],
        ['question' => 'Can I listen to music on multiple devices?', 'answer' => 'Yes, you can access your Music Town account on different devices.'],
    ];
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Music Town is a music streaming platform focused on music promotion and listener rewards.">
    <title>Music Town - Daily vibes, daily earnings</title>
    <?php echo $__env->make('partials.favicon', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="#top" aria-label="Music Town home">
            <?php echo $__env->make('partials.brand-mark', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <span>Music Town</span>
        </a>

        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle>
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="site-nav" data-site-nav>
            <a href="#top">HomePage</a>
            <a href="#about">About Us</a>
            <a href="#songs">Songs</a>
            <a href="#faq">FAQ</a>
            <a href="#contact">Contact</a>
        </nav>

        <div class="auth-links">
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('profile')); ?>">Dashboard</a>
                <a class="button button-small" href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form-welcome').submit();">Logout</a>
                <form id="logout-form-welcome" method="POST" action="<?php echo e(route('logout')); ?>" style="display:none;"><?php echo csrf_field(); ?></form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>">Login</a>
                <a class="button button-small" href="<?php echo e(route('signup')); ?>">Signup</a>
            <?php endif; ?>
        </div>
    </header>

    <main id="top">
        <section class="hero-section">
            <div class="hero-bg" aria-hidden="true"></div>
            <div class="hero-content">
                <p class="eyebrow">01</p>
                <h1>Daily vibes,<br>daily earnings!</h1>
                <p>Music Town is a music streaming platform where you earn ₦5 for every second you listen.</p>
                <div class="hero-actions">
                    <a class="button" href="<?php echo e(route('signup')); ?>">Sign Up</a>
                    <a class="button button-ghost" href="#songs">Explore Songs</a>
                </div>
            </div>

            <div class="player-console" aria-label="Now playing">
                <div class="disc-art">
                    <span></span>
                </div>
                <div class="track-meta">
                    <p>Now Playing</p>
                    <strong>Music Town Session</strong>
                    <small>Music Town Live Radio</small>
                </div>
                <div class="wave-bars" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="progress-line" aria-hidden="true">
                    <span></span>
                </div>
            </div>

            <aside class="hero-panel" aria-label="Music Town highlights">
                <div>
                    <span>02</span>
                    <strong>Turning playlists into profits</strong>
                </div>
                <div>
                    <span>03</span>
                    <strong>Daily entertainment, daily revenue payments</strong>
                </div>
            </aside>
        </section>

        <section class="songs-section" id="songs">
            <div class="section-heading">
                <p class="eyebrow">Top Songs</p>
                <h2>Fresh tracks for every mood.</h2>
            </div>

            <div class="song-grid">
                <?php $__currentLoopData = $songs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $song): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="song-card" href="<?php echo e(route('signup')); ?>">
                        <span class="play-icon" aria-hidden="true"></span>
                        <span>
                            <strong><?php echo e($song['title']); ?></strong>
                            <small><?php echo e($song['artist']); ?></small>
                        </span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="features-section">
            <div class="section-heading">
                <p class="eyebrow">How It Works</p>
                <h2>Listen, earn, withdraw.</h2>
            </div>

            <div class="feature-list">
                <a href="<?php echo e(route('signup')); ?>">
                    <span>01</span>
                    Create your free account
                </a>
                <a href="<?php echo e(route('signup')); ?>">
                    <span>02</span>
                    Stream songs and earn ₦5 per second
                </a>
                <a href="<?php echo e(route('signup')); ?>">
                    <span>03</span>
                    Withdraw to your bank account
                </a>
            </div>
        </section>

        <section class="stream-section" id="about">
            <div class="stream-image">
                <img src="https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80" alt="Artist singing into a microphone on stage">
            </div>
            <div class="stream-copy">
                <p class="eyebrow">Stream. Play. Earn.</p>
                <h2>Listen to music and earn rewards.</h2>
                <p>Every second you stream earns you ₦5. Build your balance, upgrade your tier, and withdraw directly to your bank account. Simple, transparent, and rewarding.</p>
                <a class="button" href="<?php echo e(route('signup')); ?>">Get Started</a>
            </div>
        </section>

        <section class="stats-section">
            <div>
                <p class="eyebrow">Music Town</p>
                <h2>Your music, your earnings, your way.</h2>
                <p>Start streaming and earning today!</p>
            </div>
            <div class="stats">
                <strong>Listen<span>Stream music</span></strong>
                <strong>Earn<span>Get paid to listen</span></strong>
                <strong>Withdraw<span>Bank transfer</span></strong>
            </div>
        </section>

        <section class="faq-section" id="faq">
            <div class="section-heading">
                <p class="eyebrow">FAQs</p>
                <h2>Answers before your first stream.</h2>
            </div>

            <div class="faq-list">
                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details <?php if($loop->first): ?> open <?php endif; ?>>
                        <summary><?php echo e($faq['question']); ?></summary>
                        <p><?php echo e($faq['answer']); ?></p>
                    </details>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="contact">
        <div>
            <span class="footer-number">13</span>
            <h2>Unleash your soundtrack for life.</h2>
        </div>
        <div class="contact-block">
            <p>Get in touch</p>
            <a href="mailto:hello@musictown.test">hello@musictown.test</a>
            <div class="social-links">
                <a href="https://www.instagram.com" target="_blank" rel="noreferrer">Instagram</a>
                <a href="<?php echo e(route('signup')); ?>">Signup</a>
                <a href="#songs">Songs</a>
            </div>
        </div>
    </footer>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\music\resources\views/welcome.blade.php ENDPATH**/ ?>