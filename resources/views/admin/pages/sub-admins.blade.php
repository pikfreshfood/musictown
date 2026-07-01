@extends('layouts.admin')
@section('title', 'Sub Admins')
@section('page-title', 'Sub Admin Management')

@section('content')
    @if (Auth::user()->role === 'super_admin')
        <div class="admin-card" style="margin-bottom:24px;">
            <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Create New Sub Admin</p>
            <form method="POST" action="{{ route('admin.sub-admins.create') }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
                @csrf
                <label class="form-label">
                    Full name
                    <input type="text" name="name" placeholder="Full name" required class="input-field">
                </label>
                <label class="form-label">
                    Email address
                    <input type="email" name="email" placeholder="email@example.com" required class="input-field">
                </label>
                <label class="form-label">
                    Phone number
                    <input type="tel" name="phone" placeholder="+234 800 000 0000" required class="input-field">
                </label>
                <label class="form-label">
                    Password
                    <input type="password" name="password" placeholder="Min 6 characters" required class="input-field">
                </label>
                <div style="display:flex;align-items:end;">
                    <button class="btn btn-primary" type="submit">Create Sub Admin</button>
                </div>
            </form>
        </div>
    @endif

    <div class="admin-card">
        <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Current Admins</p>
        @if ($admins->count() > 0)
            <div style="display:grid;gap:10px;">
                @foreach ($admins as $admin)
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:12px 0;border-bottom:1px solid rgba(72,181,255,0.06);">
                        <div>
                            <strong style="font-size:0.95rem;">{{ $admin->name }}</strong>
                            <small style="display:block;color:var(--muted);font-size:0.8rem;">{{ $admin->email }} &middot; Role: {{ $admin->role ?? 'admin' }}</small>
                        </div>
                        @if (Auth::user()->role === 'super_admin')
                            <a class="btn btn-danger btn-sm" href="{{ route('admin.sub-admins.delete', $admin->id) }}" onclick="return confirm('Delete sub-admin {{ $admin->name }}?')">Delete</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--muted);margin:0;">No sub-admins created yet.</p>
        @endif
    </div>
@endsection
