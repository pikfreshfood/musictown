@extends('layouts.admin')
@section('title', 'Settings')
@section('page-title', 'Admin Settings')

@section('content')
    <div class="admin-card" style="max-width:500px;">
        <p style="font-weight:700;margin:0 0 16px;font-size:1rem;">Change Password</p>
        <form method="POST" action="{{ route('admin.settings.password') }}" style="display:grid;gap:14px;">
            @csrf
            <label class="form-label">
                Current password
                <input type="password" name="current_password" placeholder="Enter current password" required class="input-field">
            </label>
            <label class="form-label">
                New password
                <input type="password" name="new_password" placeholder="Min 6 characters" required class="input-field">
            </label>
            <label class="form-label">
                Confirm new password
                <input type="password" name="new_password_confirmation" placeholder="Repeat new password" required class="input-field">
            </label>
            <button class="btn btn-primary" type="submit" style="justify-self:start;">Update Password</button>
        </form>
    </div>
@endsection
