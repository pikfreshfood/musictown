@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Premium</th>
                    <th>Joined</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td style="font-weight:600;">{{ $u->name }}</td>
                        <td style="color:var(--muted);">{{ $u->email }}</td>
                        <td style="color:var(--muted);">{{ $u->phone ?? '—' }}</td>
                        <td style="font-weight:700;color:var(--gold);">₦{{ number_format($u->balance, 2) }}</td>
                        <td>@if ($u->is_premium) <span style="color:var(--green);font-weight:700;font-size:0.8rem;">YES</span> @else <span style="color:var(--muted);font-size:0.8rem;">No</span> @endif</td>
                        <td style="color:var(--muted);font-size:0.85rem;">{{ $u->created_at->format('M d, Y') }}</td>
                        <td style="text-align:center;">
                            <a class="btn btn-danger btn-sm" href="{{ route('admin.users.delete', $u->id) }}" onclick="return confirm('Delete user {{ $u->name }}?')">Delete</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:32px;text-align:center;color:var(--muted);">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="pagination-wrap">{{ $users->links('pagination::tailwind') }}</div>
    @endif
@endsection
