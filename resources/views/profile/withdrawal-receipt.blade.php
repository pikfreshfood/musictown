@extends('layouts.user')

@section('title', 'Withdrawal Receipt')
@section('page-title', 'Receipt')

@section('content')
        <section style="max-width:500px;margin:0 auto;">
            <div class="receipt-card" style="text-align:center;">
                <div style="width:56px;height:56px;border-radius:50%;background:rgba(59,130,246,0.15);border:2px solid rgba(59,130,246,0.5);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h2 style="font-size:1.3rem;font-weight:800;margin:0 0 2px;">Withdrawal Receipt</h2>
                <p style="color:var(--muted);font-size:0.75rem;margin:0 0 20px;">{{ $withdrawal->created_at->format('M d, Y \a\t h:i A') }}</p>
            </div>

            <div class="receipt-card" style="margin-top:16px;">
                <h3 style="font-size:0.8rem;font-weight:700;color:var(--blue-soft);margin:0 0 12px;text-transform:uppercase;letter-spacing:1px;">Transfer Details</h3>
                <div class="receipt-rows">
                    <div class="receipt-row">
                        <span class="receipt-label">Amount Paid</span>
                        <span class="receipt-value">₦{{ number_format($withdrawal->amount, 2) }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Currency</span>
                        <span class="receipt-value">NGN</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Sent to</span>
                        <span class="receipt-value">{{ $withdrawal->account_name }}</span>
                    </div>
                    <div class="receipt-row" style="border-bottom:0;">
                        <span class="receipt-label">You received</span>
                        <span class="receipt-value" style="color:#60a5fa;">₦{{ number_format($withdrawal->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="receipt-card" style="margin-top:16px;">
                <h3 style="font-size:0.8rem;font-weight:700;color:var(--blue-soft);margin:0 0 12px;text-transform:uppercase;letter-spacing:1px;">Recipient Details</h3>
                <div class="receipt-rows">
                    <div class="receipt-row">
                        <span class="receipt-label">Name</span>
                        <span class="receipt-value">{{ $withdrawal->account_name }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Status</span>
                        <span class="receipt-value receipt-status">Successful</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Account No.</span>
                        <span class="receipt-value receipt-mono">{{ $withdrawal->account_number }}</span>
                    </div>
                    <div class="receipt-row">
                        <span class="receipt-label">Bank</span>
                        <span class="receipt-value">{{ $withdrawal->bank_name }}</span>
                    </div>
                    <div class="receipt-row" style="border-bottom:0;">
                        <span class="receipt-label">Transaction ID</span>
                        <span class="receipt-value receipt-mono" style="color:var(--blue-soft);font-size:0.7rem;">{{ $transactionId }}</span>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:20px;">
                <a href="{{ route('profile') }}" class="button auth-submit receipt-btn" style="background:linear-gradient(135deg,rgba(59,130,246,0.2),rgba(59,130,246,0.2));">Home</a>
                <a href="{{ route('profile.withdrawal') }}" class="button auth-submit receipt-btn">Withdrawal</a>
            </div>
        </section>

    <style>
        .auth-submit {
            border: 0;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
        .receipt-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 12px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(59,130,246,0.12);
            padding: clamp(18px, 3vw, 28px);
        }
        .receipt-rows {
            display: grid;
            gap: 0;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 7px 0;
            border-bottom: 1px solid rgba(72,181,255,0.08);
            gap: 8px;
        }
        .receipt-label {
            color: var(--muted);
            font-size: 0.75rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .receipt-value {
            font-weight: 600;
            color: white;
            font-size: 0.8rem;
            text-align: right;
            word-break: break-word;
            max-width: 60%;
        }
        .receipt-mono {
            font-family: monospace;
            font-size: 0.7rem;
        }
        .receipt-status {
            color: #60a5fa;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.7rem;
        }
        .receipt-btn {
            flex: 1;
            text-decoration: none;
            text-align: center;
            font-size: 0.8rem;
        }
    </style>
@endsection
