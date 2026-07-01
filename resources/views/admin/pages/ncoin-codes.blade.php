@php
    $title = 'Ncoin Codes';
@endphp

@extends('layouts.admin')

@section('content')
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <h2>Ncoin Codes</h2>
    </div>

    @if (session('success'))
        <p class="form-message success-message">{!! session('success') !!}</p>
    @endif

    @if (session('error'))
        <p class="form-message error-message">{{ session('error') }}</p>
    @endif

    <div class="admin-card" style="max-width:500px;margin-bottom:24px;padding:20px 24px;">
        <p style="font-weight:700;margin:0 0 12px;">Generate Ncoin Code</p>
        <p style="color:var(--muted);font-size:0.85rem;margin:0 0 16px;">Codes are used by premium users to authorize withdrawals. Share the generated code with the user.</p>
        <form method="POST" action="{{ route('admin.ncoin-codes.generate') }}" style="display:flex;gap:10px;align-items:end;">
            @csrf
            <label class="form-label" style="flex:1;margin:0;">
                Quantity
                <input type="number" name="count" value="1" min="1" max="20" class="input-field" style="text-align:center;">
            </label>
            <button class="btn btn-primary" type="submit" style="white-space:nowrap;">Generate</button>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Used By</th>
                    <th>Used At</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($codes as $code)
                    <tr>
                        <td style="font-family:monospace;font-size:1.1rem;font-weight:700;letter-spacing:2px;">{{ $code->code }}</td>
                        <td>
                            @if ($code->is_used)
                                <span style="color:var(--muted);font-weight:700;text-transform:uppercase;font-size:0.8rem;">Used</span>
                            @else
                                <span style="color:var(--green);font-weight:700;text-transform:uppercase;font-size:0.8rem;">Available</span>
                            @endif
                        </td>
                        <td>
                            @if ($code->used_by_user_id)
                                {{ $code->used_by_user_id }}
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($code->used_at)
                                {{ $code->used_at->format('M d, Y H:i') }}
                            @else
                                <span style="color:var(--muted);">—</span>
                            @endif
                        </td>
                        <td>{{ $code->created_at->format('M d, Y') }}</td>
                        <td>
                            @if (!$code->is_used)
                                <a class="btn-small btn-reject" href="{{ route('admin.ncoin-codes.delete', $code->id) }}" onclick="return confirm('Delete this code?')">Delete</a>
                            @else
                                <span style="color:var(--muted);font-size:0.85rem;">—</span>
                            </td>
                            @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--muted);padding:32px;">No codes generated yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($codes->hasPages())
        <div class="pagination-wrap" style="margin-top:24px;">
            {{ $codes->links('pagination::tailwind') }}
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
        .btn-reject {
            background: rgba(255,50,50,0.12);
            border: 1px solid rgba(255,50,50,0.4);
            color: #ff6b6b;
        }
    </style>
@endsection
