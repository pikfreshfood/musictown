@extends('layouts.admin')
@section('title', 'Payment Account')
@section('page-title', 'Payment Account')

@section('content')
    <div class="admin-card" style="max-width:600px;">
        @if ($account)
            <div style="margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--line);">
                <p style="color:var(--muted);font-size:0.8rem;font-weight:700;text-transform:uppercase;margin:0 0 12px;">Current Active Account</p>
                <p style="font-size:1.2rem;font-weight:700;margin:0 0 4px;">{{ $account->bank_name }}</p>
                <p style="font-size:1.4rem;font-weight:800;color:var(--gold);letter-spacing:2px;margin:0 0 4px;">{{ $account->account_number }}</p>
                <p style="color:var(--muted);margin:0;">{{ $account->account_name }}</p>
            </div>
            <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Update Payment Account</p>
            <p style="color:var(--muted);font-size:0.85rem;margin:0 0 16px;">Enter your 6-digit PIN to update the account details.</p>
        @else
            <p style="color:var(--muted);margin-bottom:24px;">No payment account set. Users will not see bank details for premium upgrade.</p>
            <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Set Up Payment Account</p>
            <p style="color:var(--muted);font-size:0.85rem;margin:0 0 16px;">Create a 6-digit PIN to secure your payment account.</p>
        @endif

        <form method="POST" action="{{ route('admin.payment-account.save') }}" style="display:grid;gap:14px;">
            @csrf
            <label class="form-label">
                Bank name
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="GTBank" required class="input-field">
            </label>
            <label class="form-label">
                Account number
                <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="0123456789" required class="input-field">
            </label>
            <label class="form-label">
                Account name
                <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="Full name on account" required class="input-field">
            </label>
            <label class="form-label">
                Ncoin amount (₦)
                <input type="number" name="ncoin_amount" value="{{ old('ncoin_amount', $account->ncoin_amount ?? '') }}" placeholder="e.g. 5000" min="0" step="0.01" class="input-field">
                <span style="font-size:0.8rem;color:var(--muted);font-weight:400;">Amount users must pay for Ncoin. Leave empty for no fixed price.</span>
            </label>

            @if ($account)
                <label class="form-label">
                    Current 6-digit PIN
                    <input type="password" name="pin" placeholder="Enter your 6-digit PIN" required class="input-field" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                    @error('pin')
                        <span style="color:#ef4444;font-size:0.8rem;">{{ $message }}</span>
                    @enderror
                </label>
            @else
                <label class="form-label">
                    Create 6-digit PIN
                    <input type="password" name="pin" placeholder="Enter 6-digit PIN" required class="input-field" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                </label>
                <label class="form-label">
                    Confirm 6-digit PIN
                    <input type="password" name="pin_confirmation" placeholder="Confirm 6-digit PIN" required class="input-field" maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                </label>
            @endif

            <button class="btn btn-primary" type="submit" style="justify-self:start;">
                {{ $account ? 'Update Account' : 'Save Account' }}
            </button>
        </form>
    </div>
@endsection
