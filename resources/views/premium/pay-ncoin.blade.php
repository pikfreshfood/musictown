@extends('layouts.user')

@section('title', 'Purchase Ncoin')
@section('page-title', 'Ncoin')

@section('content')
        <section style="max-width:600px;margin:0 auto;">

            @if ($errors->any())
                <div class="form-message error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div style="background:linear-gradient(145deg,rgba(59,130,246,0.08),rgba(59,130,246,0.04));border:1px solid rgba(59,130,246,0.3);border-radius:8px;padding:16px 20px;text-align:center;margin-bottom:32px;">
                <p style="color:var(--blue-soft);font-weight:700;margin:0;font-size:0.95rem;">Payment from OPay or Palmpay are delayed! To avoid long wait times and failed confirmations, please use Bank Transfer, Moniepoint, UBA, GTBank, or Zenith. OPay/Palmpay may take 2-6 hours — Not recommended.</p>
            </div>

            <div style="text-align:center;margin-bottom:32px;">
                <p class="eyebrow">Ncoin Purchase</p>
                <h1 style="font-size:clamp(2rem,4vw,3.5rem);margin:0;line-height:1;">Buy premium security code</h1>
                <p style="color:var(--muted);margin-top:12px;">Make payment to the account below and upload your proof of payment.</p>
                <p style="color:var(--blue-soft);font-size:0.85rem;font-weight:600;margin-top:8px;">&#9432; You can purchase Ncoin at any time</p>
            </div>

            @if ($paymentAccount)
                <div style="background:linear-gradient(145deg,rgba(12,24,48,0.88),rgba(4,9,18,0.94));border:1px solid rgba(59,130,246,0.42);border-radius:12px;padding:clamp(24px,4vw,36px);text-align:center;margin-bottom:28px;box-shadow:0 0 48px rgba(59,130,246,0.08);">
                    <p style="color:var(--blue-soft);font-size:0.85rem;font-weight:800;text-transform:uppercase;margin:0 0 16px;">Send payment to</p>
                    <p style="font-size:1.3rem;font-weight:800;margin:0 0 6px;">{{ $paymentAccount->bank_name }}</p>
                    <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin:0 0 6px;">
                        <span style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;color:var(--blue-soft);letter-spacing:2px;" id="acctNum">{{ $paymentAccount->account_number }}</span>
                        <button onclick="copyText('acctNum')" style="background:none;border:none;cursor:pointer;padding:4px;display:flex;align-items:center;color:var(--muted);transition:color .15s;" title="Copy account number" onmouseover="this.style.color='white'" onmouseout="this.style.color=''">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                        <span id="acctNumCopied" style="font-size:0.75rem;color:var(--blue-soft);display:none;">Copied!</span>
                    </div>
                    <div style="margin-top:4px;">
                        <span style="color:var(--muted);">{{ $paymentAccount->account_name }}</span>
                    </div>
                    @if ($paymentAccount->ncoin_amount)
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(59,130,246,0.2);">
                            <span style="color:var(--muted);font-size:0.85rem;">Ncoin price:</span>
                            <span style="color:var(--blue-soft);font-weight:800;font-size:1.3rem;">₦{{ number_format($paymentAccount->ncoin_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div style="background:linear-gradient(145deg,rgba(220,38,38,0.08),rgba(220,38,38,0.04));border:1px solid rgba(220,38,38,0.3);border-radius:12px;padding:24px;text-align:center;margin-bottom:28px;">
                    <p style="color:#f87171;font-weight:700;margin:0;">No payment account set yet. Please check back later.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('premium.ncoin.submit') }}" enctype="multipart/form-data" style="display:grid;gap:16px;">
                    @csrf
                    <label style="display:grid;gap:8px;font-weight:700;font-size:0.9rem;color:#dce7f8;">
                        Amount you paid (₦)
                        <input type="number" name="amount" placeholder="Enter amount" min="1" step="0.01" value="{{ old('amount', $paymentAccount->ncoin_amount ?? '') }}" required style="background:rgba(2,6,14,0.82);border:1px solid rgba(59,130,246,0.25);border-radius:8px;color:white;min-height:52px;padding:0 16px;outline:0;">
                    </label>

                    <label style="display:grid;gap:8px;font-weight:700;font-size:0.9rem;color:#dce7f8;">
                        Upload payment proof (screenshot or PDF)
                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required style="background:rgba(2,6,14,0.82);border:1px solid rgba(59,130,246,0.25);border-radius:8px;color:white;min-height:52px;padding:10px 16px;outline:0;">
                    </label>

                    <button class="button auth-submit" type="button" id="payBtn" style="border:0;cursor:pointer;width:100%;">I have paid</button>
                </form>

            <div id="paymentFailed" style="display:none;background:linear-gradient(145deg,rgba(255,50,50,0.1),rgba(255,50,50,0.05));border:2px solid rgba(255,50,50,0.4);border-radius:16px;padding:36px 24px;text-align:center;margin-top:24px;">
                <p style="font-size:2.5rem;margin:0 0 8px;">&#10060;</p>
                <h2 style="font-size:1.5rem;margin:0 0 8px;color:#f87171;">Payment Failed</h2>
                <p style="color:var(--muted);font-size:1rem;margin:0 0 4px;">Your payment could not be processed.</p>
                <p style="display:flex;align-items:center;justify-content:center;gap:10px;color:var(--muted);font-size:1rem;margin:0 0 20px;">
                    Contact admin on Telegram
                    @if ($telegramUsername)
                        <a href="https://t.me/{{ $telegramUsername }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;color:#0088cc;text-decoration:none;font-weight:700;padding:6px 14px;border-radius:8px;background:rgba(0,136,204,0.12);border:1px solid rgba(0,136,204,0.25);transition:background .2s;" onmouseover="this.style.background='rgba(0,136,204,0.22)'" onmouseout="this.style.background='rgba(0,136,204,0.12)'">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="#0088cc"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        </a>
                    @else
                        <span style="color:var(--muted);font-size:0.9rem;">(no admin contact available)</span>
                    @endif
                </p>
            </div>

            <p style="text-align:center;margin-top:20px;">
                <a href="{{ route('premium.index') }}" style="color:var(--muted);font-weight:700;">&larr; Back</a>
            </p>
        </section>

    <script>
        document.getElementById('payBtn').addEventListener('click', function() {
            document.getElementById('paymentFailed').style.display = 'block';
            this.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });

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
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }
        .error-message {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #f87171;
        }
        .error-message p { margin: 0; }
        .error-message p + p { margin-top: 6px; }
        input:focus {
            border-color: rgba(59, 130, 246, 0.74) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.16) !important;
        }
    </style>
@endsection
