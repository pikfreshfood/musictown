<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Ncoin - PulseWave</title>
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
            <span style="color:var(--gold);font-weight:700;">{{ $user->name }}</span>
        </div>
    </header>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>

    <main style="padding: 120px clamp(20px, 5vw, 72px) 40px;">
        <section style="max-width:600px;margin:0 auto;">

            @if ($errors->any())
                <div class="form-message error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($pendingNcoin)
                <div style="background:linear-gradient(145deg,rgba(72,181,255,0.08),rgba(72,181,255,0.04));border:1px solid rgba(72,181,255,0.3);border-radius:8px;padding:16px 20px;text-align:center;margin-bottom:32px;">
                    <p style="color:var(--gold);font-weight:700;margin:0;">Your Ncoin payment of ₦{{ number_format($pendingNcoin->amount, 2) }} is pending approval.</p>
                    <p style="color:var(--muted);font-size:0.85rem;margin:6px 0 0;">Submitted {{ $pendingNcoin->created_at->diffForHumans() }}</p>
                    <p style="color:var(--muted);font-size:0.85rem;margin:6px 0 0;">Please contact admin for approval.</p>
                </div>
            @endif

            <div style="text-align:center;margin-bottom:32px;">
                <p class="eyebrow">Ncoin Purchase</p>
                <h1 style="font-size:clamp(2rem,4vw,3.5rem);margin:0;line-height:1;">Buy premium security code</h1>
                <p style="color:var(--muted);margin-top:12px;">Make payment to the account below and upload your proof of payment.</p>
                <p style="color:var(--gold);font-size:0.85rem;font-weight:600;margin-top:8px;">&#9432; You can purchase Ncoin at any time</p>
            </div>

            @if ($paymentAccount)
                <div style="background:linear-gradient(145deg,rgba(12,24,48,0.88),rgba(4,9,18,0.94));border:1px solid rgba(255,184,77,0.42);border-radius:12px;padding:clamp(24px,4vw,36px);text-align:center;margin-bottom:28px;box-shadow:0 0 48px rgba(255,184,77,0.08);">
                    <p style="color:var(--orange);font-size:0.85rem;font-weight:800;text-transform:uppercase;margin:0 0 16px;">Send payment to</p>
                    <p style="font-size:1.3rem;font-weight:800;margin:0 0 6px;">{{ $paymentAccount->bank_name }}</p>
                    <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin:0 0 6px;">
                        <span style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;color:var(--gold);letter-spacing:2px;" id="acctNum">{{ $paymentAccount->account_number }}</span>
                        <button onclick="copyText('acctNum')" style="background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;color:var(--muted);transition:color .15s;" title="Copy account number" onmouseover="this.style.color='white'" onmouseout="this.style.color=''">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <span id="acctNumCopied" style="font-size:0.75rem;color:var(--green);display:none;">Copied!</span>
                    </div>
                    <div style="margin-top:4px;">
                        <span style="color:var(--muted);">{{ $paymentAccount->account_name }}</span>
                    </div>
                    @if ($paymentAccount->ncoin_amount)
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,184,77,0.2);">
                            <span style="color:var(--muted);font-size:0.85rem;">Ncoin price:</span>
                            <span style="color:var(--gold);font-weight:800;font-size:1.3rem;">₦{{ number_format($paymentAccount->ncoin_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div style="background:linear-gradient(145deg,rgba(255,50,50,0.08),rgba(255,50,50,0.04));border:1px solid rgba(255,50,50,0.3);border-radius:12px;padding:24px;text-align:center;margin-bottom:28px;">
                    <p style="color:#ff6b6b;font-weight:700;margin:0;">No payment account set yet. Please check back later.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('premium.ncoin.submit') }}" enctype="multipart/form-data" style="display:grid;gap:16px;">
                    @csrf
                    <label style="display:grid;gap:8px;font-weight:700;font-size:0.9rem;color:#dce7f8;">
                        Amount you paid (₦)
                        <input type="number" name="amount" placeholder="Enter amount" min="1" step="0.01" value="{{ old('amount', $paymentAccount->ncoin_amount ?? '') }}" required style="background:rgba(2,6,14,0.82);border:1px solid rgba(72,181,255,0.25);border-radius:8px;color:white;min-height:52px;padding:0 16px;outline:0;">
                    </label>

                    <label style="display:grid;gap:8px;font-weight:700;font-size:0.9rem;color:#dce7f8;">
                        Upload payment proof (screenshot or PDF)
                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required style="background:rgba(2,6,14,0.82);border:1px solid rgba(72,181,255,0.25);border-radius:8px;color:white;min-height:52px;padding:10px 16px;outline:0;">
                    </label>

                    <button class="button auth-submit" type="submit" style="border:0;cursor:pointer;width:100%;">I have paid</button>
                </form>

            <p style="text-align:center;margin-top:20px;">
                <a href="{{ route('premium.index') }}" style="color:var(--muted);font-weight:700;">&larr; Back</a>
            </p>
        </section>
    </main>

    <script>
        function copyText(elId) {
            var text = document.getElementById(elId).innerText;
            navigator.clipboard.writeText(text).then(function() {
                var feedback = document.getElementById(elId + 'Copied');
                if (feedback) {
                    feedback.style.display = 'inline';
                    setTimeout(function() { feedback.style.display = 'none'; }, 2000);
                }
            }).catch(function() {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                var feedback = document.getElementById(elId + 'Copied');
                if (feedback) {
                    feedback.style.display = 'inline';
                    setTimeout(function() { feedback.style.display = 'none'; }, 2000);
                }
            });
        }
    </script>

    <style>
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .success-message {
            background: rgba(72, 181, 255, 0.12);
            border: 1px solid rgba(72, 181, 255, 0.4);
            color: #48b5ff;
        }
        .error-message {
            background: rgba(255, 50, 50, 0.12);
            border: 1px solid rgba(255, 50, 50, 0.4);
            color: #ff6b6b;
        }
        .error-message p { margin: 0; }
        .error-message p + p { margin-top: 6px; }
        input:focus {
            border-color: rgba(255, 122, 26, 0.74) !important;
            box-shadow: 0 0 0 4px rgba(20, 118, 255, 0.16) !important;
        }
    </style>
</body>
</html>
