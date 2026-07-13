@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:wrap;">
        <form method="GET" action="{{ route('admin.users') }}" style="display:flex;gap:8px;align-items:center;">
            <input name="q" value="{{ $q ?? '' }}" placeholder="Search users by name, email, phone or referral code" style="padding:8px 10px;border-radius:6px;border:1px solid var(--line);width:320px;">
            <button class="button" type="submit" style="padding:8px 12px;">Search</button>
            @if(!empty($q)) <a href="{{ route('admin.users') }}" style="margin-left:8px;color:var(--muted);">Clear</a> @endif
        </form>

        <div style="color:var(--muted);font-size:0.95rem;">Showing {{ $users->total() }} users</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Tier</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Premium</th>
                    <th>Referrer</th>
                    <th>Referrals</th>
                    <th>Account</th>
                    <th>Joined</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td style="font-weight:600;">{{ $u->name }}</td>
                        <td style="color:var(--muted);font-weight:700;">{{ $u->tier ?? '—' }}</td>
                        <td style="color:var(--muted);">{{ $u->email }}</td>
                        <td style="font-weight:700;color:var(--gold);">&#8358;{{ number_format($u->balance, 2) }}</td>
                        <td>@if ($u->is_premium) <span style="color:var(--green);font-weight:700;font-size:0.8rem;">YES</span> @else <span style="color:var(--muted);font-size:0.8rem;">No</span> @endif</td>
                        <td style="color:var(--muted);font-size:0.85rem;">@if($u->referrer) {{ $u->referrer->name }} ({{ $u->referrer->email }}) @else — @endif</td>
                        <td style="font-weight:700;color:var(--blue-soft);">{{ $u->referrals->count() }}</td>
                        <td style="color:var(--muted);font-size:0.85rem;">@if($u->paystackVirtualAccount) {{ $u->paystackVirtualAccount->bank_name }} — {{ $u->paystackVirtualAccount->account_number }} ({{ $u->paystackVirtualAccount->account_name }}) @else — @endif</td>
                        <td style="color:var(--muted);font-size:0.85rem;">{{ $u->created_at->format('M d, Y') }}</td>
                        <td style="text-align:center;">
                            <a class="btn btn-danger btn-sm" href="{{ route('admin.users.delete', $u->id) }}" onclick="return confirm('Delete user {{ $u->name }}?')">Delete</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:32px;text-align:center;color:var(--muted);">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="pagination-wrap">{{ $users->links('pagination::tailwind') }}</div>
    @endif
@endsection
