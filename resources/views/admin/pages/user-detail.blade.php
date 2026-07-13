@extends('layouts.admin')
@section('title', 'User Detail')
@section('page-title', 'User Details')

@section('content')
    <div style="max-width:900px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;">
            <div>
                <h3 style="margin:0 0 8px;">{{ $user->name }}</h3>
                <p style="margin:0 0 4px;color:var(--muted);">{{ $user->email }}</p>
                <p style="margin:0 0 6px;">Balance: <strong style="color:var(--gold);">&#8358;{{ number_format($user->balance,2) }}</strong></p>
                <p style="margin:0 0 6px;">Tier: <strong>{{ $user->tier ?? '—' }}</strong></p>
                <p style="margin:0 0 6px;">Premium: <strong>@if($user->is_premium) YES @else No @endif</strong></p>
                <p style="margin:0 0 6px;">Referrer: <strong>@if($user->referrer) {{ $user->referrer->name }} ({{ $user->referrer->email }}) @else — @endif</strong></p>
                <p style="margin:0 0 6px;">Account: <strong>@if($user->paystackVirtualAccount) {{ $user->paystackVirtualAccount->bank_name }} — {{ $user->paystackVirtualAccount->account_number }} @else — @endif</strong></p>
            </div>
            <div style="text-align:right;">
                <p style="margin:0 0 6px;color:var(--muted);">Joined {{ $user->created_at->format('M d, Y') }}</p>
                <a href="{{ route('admin.users') }}" class="button">Back to users</a>
            </div>
        </div>

        <hr style="margin:16px 0;">

        <h4 style="margin:0 0 12px;">Referrals ({{ $user->referrals->count() }})</h4>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tier</th>
                        <th>Premium</th>
                        <th>Account</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->referrals as $r)
                        <tr>
                            <td style="font-weight:600;">{{ $r->name }}</td>
                            <td style="color:var(--muted);">{{ $r->email }}</td>
                            <td style="color:var(--muted);">{{ $r->tier ?? '—' }}</td>
                            <td>@if($r->is_premium) <span style="color:var(--green);font-weight:700;">YES</span> @else <span style="color:var(--muted);">No</span> @endif</td>
                            <td style="color:var(--muted);">@if($r->paystackVirtualAccount) {{ $r->paystackVirtualAccount->bank_name }} — {{ $r->paystackVirtualAccount->account_number }} @else — @endif</td>
                            <td style="color:var(--muted);font-size:0.85rem;">{{ $r->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:24px;text-align:center;color:var(--muted);">No referrals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
