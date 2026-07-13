@extends('layouts.user')

@section('title', 'Fund Wallet')
@section('page-title', 'Wallet')
@section('meta-description', 'Fund your Music Town wallet with a dedicated virtual account.')

@section('content')

        <section class="wallet-grid">
            <div class="wallet-panel">
                <p class="eyebrow">Fund Wallet</p>
                <h1>Pay by bank transfer.</h1>
                <p class="wallet-copy">Enter the amount you want to fund, then transfer to your dedicated account. Your balance is credited only after Paystack confirms the transfer.</p>

                @if (session('success'))
                    <div class="form-message success-message">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="form-message error-message">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('profile.wallet.fund') }}" class="fund-form">
                    @csrf
                    <label for="amount">Amount</label>
                    <div class="amount-row">
                        <span>₦</span>
                        <input id="amount" name="amount" type="number" min="100" step="100" value="{{ old('amount', $pendingFunding?->amount) }}" placeholder="15000" required>
                    </div>
                    <button class="button" type="submit">Create Funding Request</button>
                </form>
            </div>

            <div class="account-card">
                <div class="account-card-top">
                    <span>Available Balance</span>
                    <strong id="balance-display">₦{{ number_format($user->balance, 2) }}</strong>
                </div>

                @if ($accountError)
                    <div class="form-message error-message" style="margin-top:12px;">{{ $accountError }}</div>
                @endif

                <div class="account-detail">
                    <span>Bank</span>
                    <strong>{{ $virtualAccount?->bank_name ?: 'Pending bank assignment' }}</strong>
                </div>

                <div class="account-detail">
                    <span>Account Number</span>
                    <div class="copy-row">
                        <strong id="account-number">{{ $virtualAccount?->account_number ?: '---' }}</strong>
                        <button type="button" id="copy-account">Copy</button>
                    </div>
                </div>

                <div class="account-detail">
                    <span>Account Name</span>
                    <strong>{{ $virtualAccount?->account_name ?: 'Music Town' }}</strong>
                </div>

                @if ($pendingFunding)
                    <div class="pending-box">
                        <span>Pending Amount</span>
                        <strong>₦{{ number_format($pendingFunding->amount, 2) }}</strong>
                    </div>
                    <button class="button check-btn" type="button" id="check-payment">I Have Paid</button>
                    <p id="payment-status" class="status-text">Waiting for payment...</p>
                @else
                    <p class="status-text">Create a funding request to start.</p>
                @endif
            </div>
        </section>

        <section class="transactions-section">
            <div class="section-heading">
                <p class="eyebrow">Wallet Activity</p>
                <h2 style="font-size:1.1rem;">Recent credits.</h2>
            </div>

            <div class="transaction-list">
                @forelse ($transactions as $transaction)
                    <div class="transaction-row">
                        <span>
                            <strong>₦{{ number_format($transaction->amount, 2) }}</strong>
                            <small>{{ $transaction->sender_name ?: 'Bank transfer' }}</small>
                        </span>
                        <small>{{ $transaction->created_at->format('M j, Y g:i A') }}</small>
                    </div>
                @empty
                    <div class="empty-state">No wallet credits yet.</div>
                @endforelse
            </div>
        </section>
    </main>

    <script>
        const copyBtn = document.getElementById('copy-account');
        const accountNumber = document.getElementById('account-number');
        const checkBtn = document.getElementById('check-payment');
        const statusText = document.getElementById('payment-status');
        const balanceDisplay = document.getElementById('balance-display');

        if (copyBtn && accountNumber) {
            copyBtn.addEventListener('click', async function() {
                await navigator.clipboard.writeText(accountNumber.textContent.trim());
                copyBtn.textContent = 'Copied';
                setTimeout(function() {
                    copyBtn.textContent = 'Copy';
                }, 1800);
            });
        }

        if (checkBtn && statusText) {
            checkBtn.addEventListener('click', async function() {
                checkBtn.disabled = true;
                statusText.textContent = 'Checking payment...';

                try {
                    const response = await fetch('{{ route('profile.wallet.check') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();
                    statusText.textContent = data.message || 'Unable to check payment.';

                    if (data.balance && balanceDisplay) {
                        balanceDisplay.textContent = '₦' + parseFloat(data.balance).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    }

                    if (data.status === 'confirmed') {
                        setTimeout(function() {
                            location.reload();
                        }, 1200);
                    }
                } catch (error) {
                    statusText.textContent = 'Could not check payment. Please try again.';
                } finally {
                    checkBtn.disabled = false;
                }
            });
        }
    </script>

    <style>
        .wallet-page {
            padding: 120px clamp(20px, 5vw, 72px) 48px;
        }
        .wallet-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 380px);
            gap: 24px;
            max-width: 980px;
            margin: 0 auto;
            align-items: start;
        }
        .wallet-panel,
        .account-card,
        .transactions-section {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 10px;
            padding: clamp(22px, 4vw, 34px);
            box-shadow: 0 24px 70px rgba(0,0,0,0.36);
        }
        .wallet-panel h1 {
            color: white;
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin: 0 0 12px;
        }
        .wallet-copy,
        .status-text {
            color: var(--muted);
            line-height: 1.6;
        }
        .fund-form {
            margin-top: 24px;
        }
        .fund-form label,
        .account-detail span,
        .pending-box span,
        .account-card-top span {
            color: var(--muted);
            display: block;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .amount-row {
            align-items: center;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--line);
            border-radius: 8px;
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            padding: 0 14px;
        }
        .amount-row span {
            color: var(--gold);
            font-weight: 800;
        }
        .amount-row input {
            background: transparent;
            border: 0;
            color: white;
            flex: 1;
            font-size: 1.25rem;
            outline: none;
            padding: 16px 0;
            width: 100%;
        }
        .account-card-top {
            border-bottom: 1px solid var(--line);
            margin-bottom: 18px;
            padding-bottom: 18px;
        }
        .account-card-top strong {
            color: white;
            font-size: 2rem;
        }
        .account-detail,
        .pending-box {
            margin-top: 18px;
        }
        .account-detail strong,
        .pending-box strong {
            color: white;
            font-size: 1.1rem;
        }
        .copy-row {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: space-between;
        }
        .copy-row button {
            background: rgba(59,130,246,0.14);
            border: 1px solid rgba(59,130,246,0.5);
            border-radius: 8px;
            color: var(--blue-soft);
            cursor: pointer;
            font-weight: 800;
            padding: 8px 12px;
        }
        .check-btn {
            margin-top: 22px;
            width: 100%;
        }
        .check-btn:disabled {
            opacity: 0.6;
            pointer-events: none;
        }
        .transactions-section {
            max-width: 980px;
            margin: 28px auto 0;
        }
        .transaction-list {
            display: grid;
            gap: 12px;
        }
        .transaction-row,
        .empty-state {
            align-items: center;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--line);
            border-radius: 8px;
            color: white;
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
        }
        .transaction-row small {
            color: var(--muted);
        }
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 16px 0;
        }
        .success-message {
            background: rgba(37, 211, 102, 0.12);
            border: 1px solid rgba(37, 211, 102, 0.4);
            color: #7ee7a0;
        }
        .error-message {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #f87171;
        }
        @media (max-width: 820px) {
            .wallet-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
