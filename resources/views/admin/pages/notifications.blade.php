@extends('layouts.admin')
@section('title', 'Notifications')
@section('page-title', 'Send Promotional Email')

@section('content')
    <div class="admin-card" style="max-width:800px;">
        <p style="font-weight:700;margin:0 0 16px;font-size:1rem;">Notify users by email</p>
        <p style="margin:0 0 20px;color:var(--muted);">This form sends a single promotional email to all non-admin users. Leave recipients empty to use all extracted user emails.</p>

        <form method="POST" action="{{ route('admin.notifications.send') }}" enctype="multipart/form-data" style="display:grid;gap:16px;">
            @csrf
            <label class="form-label">
                Email subject
                <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Enter email subject" required class="input-field">
            </label>

            <label class="form-label">
                Message body
                <textarea name="message" rows="8" placeholder="Write your promotional message here" required class="input-field" style="min-height:220px;resize:vertical;">{{ old('message') }}</textarea>
            </label>

            <label class="form-label">
                Upload CSV file (required)
                <input type="file" name="recipients_csv" accept=".csv,text/csv" required class="input-field" style="padding:10px 12px;" />
                <span style="font-size:0.85rem;color:var(--muted);">One email per line or comma-separated values. The CSV file must include valid email addresses.</span>
            </label>

            <p style="margin:0;color:var(--muted);font-size:0.9rem;">If a CSV file is uploaded, its valid email addresses will be used. Otherwise all non-admin user emails are used.</p>

            <button class="btn btn-primary" type="submit" style="justify-self:start;">Send Notification</button>
        </form>
    </div>
@endsection
