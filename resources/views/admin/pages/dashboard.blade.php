@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <p class="stat-label">Total Users</p>
            <p class="stat-number">{{ $stats['users'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Songs</p>
            <p class="stat-number">{{ $stats['songs'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Pending Payments</p>
            <p class="stat-number">{{ $stats['pending_payments'] }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total User Balance</p>
            <p class="stat-number">₦{{ number_format($stats['total_balance'], 2) }}</p>
        </div>
    </div>

    <div class="admin-card">
        <p style="font-weight:700;margin:0 0 16px;font-size:1rem;">Recent Users</p>
        @if ($recentUsers->count() > 0)
            <div style="display:grid;gap:10px;">
                @foreach ($recentUsers as $u)
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:10px 0;border-bottom:1px solid rgba(72,181,255,0.06);">
                        <div>
                            <strong style="font-size:0.95rem;">{{ $u->name }}</strong>
                            <small style="display:block;color:var(--muted);font-size:0.8rem;">{{ $u->email }}</small>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-weight:700;color:var(--gold);">₦{{ number_format($u->balance, 2) }}</span>
                            <small style="display:block;color:var(--muted);font-size:0.75rem;">{{ $u->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--muted);margin:0;">No users registered yet.</p>
        @endif
    </div>
@endsection
