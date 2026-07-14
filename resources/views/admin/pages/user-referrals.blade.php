@extends('layouts.admin')
@section('title', 'User Referrals')
@section('page-title', 'Referral Details')

@section('content')
    <div style="max-width:1000px;margin:0 auto;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 6px;">{{ $user->name }}'s Referrals</h3>
                <p style="margin:0;color:var(--muted);">{{ $user->referrals->count() }} referral(s)</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="{{ route('admin.users') }}" class="button">Back to Users</a>
                <a href="{{ route('admin.users.show', $user->id) }}" class="button button-secondary">User Profile</a>
            </div>
        </div>

        <div class="table-wrap" style="margin-top:18px;">
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
                    @forelse($user->referrals as $referral)
                        <tr>
                            <td style="font-weight:600;">{{ $referral->name }}</td>
                            <td style="color:var(--muted);">{{ $referral->email }}</td>
                            <td style="color:var(--muted);">{{ $referral->tier ?? '—' }}</td>
                            <td>@if($referral->is_premium) <span style="color:var(--green);font-weight:700;">YES</span> @else <span style="color:var(--muted);">No</span> @endif</td>
                            <td style="color:var(--muted);">
                                @if($referral->paystackVirtualAccount)
                                    {{ $referral->paystackVirtualAccount->bank_name }} — {{ $referral->paystackVirtualAccount->account_number }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="color:var(--muted);font-size:0.85rem;">{{ $referral->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:24px;text-align:center;color:var(--muted);">No referrals found for this user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
