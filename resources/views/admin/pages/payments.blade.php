@extends('layouts.admin')
@section('title', 'Premium Payments')
@section('page-title', 'Premium Payment Requests')

@section('content')
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $p)
                    <tr>
                        <td style="font-weight:600;">{{ $p->user->name ?? 'Deleted' }}</td>
                        <td style="color:var(--muted);">{{ $p->user->email ?? '—' }}</td>
                        <td style="font-weight:700;color:var(--gold);">₦{{ number_format($p->amount, 2) }}</td>
                        <td>
                            @php
                                $badge = match($p->status) {
                                    'approved' => 'color:var(--green);',
                                    'rejected' => 'color:#ff6b6b;',
                                    default => 'color:var(--gold);',
                                };
                            @endphp
                            <span style="{{ $badge }}font-weight:700;text-transform:uppercase;font-size:0.8rem;">{{ $p->status }}</span>
                        </td>
                        <td style="color:var(--muted);font-size:0.85rem;">{{ $p->created_at->format('M d, Y h:i A') }}</td>
                        <td style="text-align:center;">
                            @if ($p->status === 'pending')
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    <a class="btn btn-green btn-sm" href="{{ route('admin.premium.approve', $p->id) }}">Approve</a>
                                    <a class="btn btn-danger btn-sm" href="{{ route('admin.premium.reject', $p->id) }}">Reject</a>
                                </div>
                            @else
                                <span style="color:var(--muted);font-size:0.8rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--muted);">No premium payment requests.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($payments->hasPages())
        <div class="pagination-wrap">{{ $payments->links('pagination::tailwind') }}</div>
    @endif
@endsection
