<?php
    $songs = [
        ['title' => 'Jesus Iye', 'artist' => 'Nathaniel Bassey'],
        ['title' => 'Village Girl', 'artist' => 'Speed Darlington'],
        ['title' => 'Calculate', 'artist' => 'Kidd Carder'],
        ['title' => 'Odo', 'artist' => 'Mr P Ft Stonebwoy'],
        ['title' => 'Sweet Love', 'artist' => 'Burna Boy'],
        ['title' => 'Laho (Remix)', 'artist' => 'Shallipopi Ft. Burna Boy'],
    ];

    $features = [
        'Streaming hub',
        'Music Promotion',
        'Artist Collaboration',
        'Curated Playlists',
        'Monetized Streaming',
        'Music Insights',
    ];

    $faqs = [
        ['question' => 'How do I register on Music Town?', 'answer' => 'Joining Music Town is easy. Simply contact one of our verified subscription merchants to purchase N-coin for registration.'],
        ['question' => 'Do I need a referral to get paid?', 'answer' => 'No, you do not need to refer anyone to get paid on Music Town.'],
        ['question' => 'How will I receive my streaming revenues?', 'answer' => 'Streaming revenues can be received via bank transfer, USDT wallet, or PayPal.'],
        ['question' => 'How can I start streaming music?', 'answer' => 'You can start streaming music as soon as you register.'],
        ['question' => 'What subscription plans do you offer?', 'answer' => 'We offer both basic and premium subscription plans.'],
        ['question' => 'What should I do if I am having trouble streaming?', 'answer' => 'If you encounter streaming issues, contact our customer care team for a swift resolution.'],
        ['question' => 'Can I download songs for offline listening?', 'answer' => 'Yes, with our premium subscription plan, you can download and listen to songs offline.'],
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
            <a href="#songs">Subscription</a>
            <a href="#terms">Terms</a>
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
                <p>Music Town is a music streaming platform focused on music promotion and listener rewards.</p>
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
                <p class="eyebrow">Our Features</p>
                <h2>Built for listeners, artists, and promoters.</h2>
            </div>

            <div class="feature-list">
                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('signup')); ?>">
                        <span><?php echo e(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)); ?></span>
                        <?php echo e($feature); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>

        <section class="stream-section" id="about">
            <div class="stream-image">
                <img src="https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&w=1200&q=80" alt="Artist singing into a microphone on stage">
            </div>
            <div class="stream-copy">
                <p class="eyebrow">Stream. Play. Vibe.</p>
                <h2>Explore songs. Create playlists. Enjoy music.</h2>
                <p>Immerse yourself in a world of endless music. Discover songs you love, create personalized playlists, and let the rhythm take over. Whether you are working, relaxing, or on the move, the perfect vibe is just a play away.</p>
                <a class="button" href="<?php echo e(route('signup')); ?>">Get Started</a>
            </div>
        </section>

        <section class="stats-section">
            <div>
                <p class="eyebrow">Music Town</p>
                <h2>Make your music your world with endless songs and perfect playlists.</h2>
                <p>Join millions of listeners today!</p>
            </div>
            <div class="stats">
                <strong>10M+<span>Listeners</span></strong>
                <strong>500K+<span>Playlists</span></strong>
                <strong>4.8+<span>Rating</span></strong>
            </div>
        </section>

        <section class="faq-section" id="terms">
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