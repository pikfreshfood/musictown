<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Submitted - PulseWave</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('profile') }}" aria-label="PulseWave home">
            <span class="brand-mark">P</span>
            <span>PulseWave</span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-site-nav>
            <a href="{{ route('profile') }}">Dashboard</a>
            <a href="{{ route('profile.settings') }}">Settings</a>
            <a href="{{ route('profile.withdrawal') }}">Withdrawal</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        </nav>
        <div class="auth-links">
            <span style="color:var(--gold);font-weight:700;">{{ Auth::user()->name }}</span>
        </div>
    </header>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>

    <main style="padding: 120px clamp(20px, 5vw, 72px) 40px;">
        <section style="max-width:600px;margin:0 auto;">
            <div style="text-align:center;background:linear-gradient(145deg,rgba(72,199,142,0.08),rgba(72,199,142,0.04));border:1px solid rgba(72,199,142,0.35);border-radius:16px;padding:48px 24px;">
                <p style="font-size:3rem;margin:0 0 16px;">&#10003;</p>
                <h1 style="font-size:clamp(1.8rem,3vw,2.5rem);margin:0 0 12px;color:var(--green);">Payment Submitted</h1>
                <p style="color:var(--muted);font-size:1.1rem;margin:0 0 8px;">Your payment proof has been received.</p>
                <p style="color:var(--gold);font-weight:700;font-size:1.1rem;margin:0 0 24px;display:flex;align-items:center;justify-content:center;gap:8px;">
                    Contact admin for approval
                    <a href="https://t.me/explore" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;color:var(--muted);text-decoration:none;transition:color .15s;" title="Contact on Telegram" onmouseover="this.style.color='#0088cc'" onmouseout="this.style.color=''">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    </a>
                </p>
                <a href="{{ route('premium.index') }}" class="button" style="display:inline-flex;">Back to dashboard</a>
            </div>
        </section>
    </main>

    <style>
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            padding: 12px 28px;
            text-decoration: none;
        }
    </style>
</body>
</html>
