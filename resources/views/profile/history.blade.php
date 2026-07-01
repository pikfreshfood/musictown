<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your withdrawal history.">
    <title>Withdrawal History - PulseWave</title>
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
            <a href="{{ route('profile.wallet') }}">Wallet</a>
            <a href="{{ route('profile.settings') }}">Settings</a>
            <a href="{{ route('profile.withdrawal') }}">Withdrawal</a>
            <a href="{{ route('profile.history') }}">History</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        </nav>
        <div class="auth-links">
            <span style="color:var(--gold);font-weight:700;">{{ $user->name }}</span>
        </div>
    </header>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>

    <main style="padding: 120px clamp(20px, 5vw, 72px) 40px;">
        <section style="max-width:700px;margin:0 auto;">
            <div class="section-heading">
                <p class="eyebrow">History</p>
                <h2>Withdrawal History</h2>
            </div>

            @if ($withdrawals->isNotEmpty())
                <div style="display:grid;gap:10px;">
                    @foreach ($withdrawals as $w)
                        <div class="history-item">
                            <div>
                                <strong style="font-size:1rem;">{{ $w->bank_name }}</strong>
                                <small style="display:block;color:var(--muted);margin-top:4px;">{{ $w->account_name }} &middot; {{ $w->account_number }}</small>
                            </div>
                            <div style="text-align:right;">
                                <strong style="font-size:1.1rem;">₦{{ number_format($w->amount, 2) }}</strong>
                                <small style="display:block;margin-top:4px;">
                                    @php
                                        $badge = match($w->status) {
                                            'approved' => 'color:var(--green);',
                                            'rejected' => 'color:#ff6b6b;',
                                            default => 'color:var(--gold);',
                                        };
                                    @endphp
                                    <span style="{{ $badge }}font-weight:700;text-transform:uppercase;font-size:0.75rem;">{{ $w->status }}</span>
                                    <span style="color:var(--muted);font-size:0.75rem;">&middot; {{ $w->created_at->format('M d, Y') }}</span>
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:40px 20px;background:var(--card-bg);border-radius:8px;border:1px solid var(--line);">
                    <p style="color:var(--muted);font-size:1rem;margin:0;">No withdrawals yet.</p>
                    <a href="{{ route('profile.withdrawal') }}" class="button auth-submit" style="display:inline-block;margin-top:16px;padding:10px 24px;text-decoration:none;">Make a withdrawal</a>
                </div>
            @endif
        </section>
    </main>

    <style>
        .history-item {
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .auth-submit {
            border: 0;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
    </style>
</body>
</html>
