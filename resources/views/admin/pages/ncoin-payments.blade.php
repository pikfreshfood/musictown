@php
    $title = 'Ncoin Payments';
@endphp

@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <h2>Ncoin Payments</h2>
    </div>

    @if (session('success'))
        <p class="form-message success-message">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p class="form-message error-message">{{ session('error') }}</p>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Proof</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>
                            <strong>{{ $payment->user->name }}</strong>
                            <small style="display:block;color:var(--muted);">{{ $payment->user->email }}</small>
                        </td>
                        <td>₦{{ number_format($payment->amount, 2) }}</td>
                        <td>
                            @if ($payment->proof_path)
                                <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank" style="color:var(--blue);font-weight:600;">View proof</a>
                            @else
                                <span style="color:var(--muted);">No proof</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match($payment->status) {
                                    'approved' => 'color:var(--green);',
                                    'rejected' => 'color:#ff6b6b;',
                                    default => 'color:var(--gold);',
                                };
                            @endphp
                            <span style="{{ $badge }}font-weight:700;text-transform:uppercase;font-size:0.8rem;">{{ $payment->status }}</span>
                        </td>
                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                        <td>
                            @if ($payment->status === 'pending')
                                <a class="btn-small btn-approve" href="{{ route('admin.ncoin.approve', $payment->id) }}" onclick="return confirm('Approve this Ncoin payment?')">Approve</a>
                                <a class="btn-small btn-reject" href="{{ route('admin.ncoin.reject', $payment->id) }}" onclick="return confirm('Reject this Ncoin payment?')">Reject</a>
                            @else
                                <span style="color:var(--muted);font-size:0.85rem;">Done</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:32px;">No Ncoin payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($payments->hasPages())
        <div class="pagination-wrap" style="margin-top:24px;">
            {{ $payments->links('pagination::tailwind') }}
        </div>
    @endif

    <style>
        .btn-small {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity 150ms ease;
        }
        .btn-small:hover { opacity: 0.8; }
        .btn-approve {
            background: rgba(72,199,142,0.15);
            border: 1px solid rgba(72,199,142,0.4);
            color: var(--green);
        }
        .btn-reject {
            background: rgba(255,50,50,0.12);
            border: 1px solid rgba(255,50,50,0.4);
            color: #ff6b6b;
        }
    </style>
@endsection
