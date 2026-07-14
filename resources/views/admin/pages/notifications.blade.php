@extends('layouts.admin')
@section('title', 'Notifications')
@section('page-title', 'Send Promotional Email')

@section('content')
    <div class="admin-card" style="max-width:800px;">
        <p style="font-weight:700;margin:0 0 16px;font-size:1rem;">Notify users by email</p>
        <p style="margin:0 0 20px;color:var(--muted);">Upload a CSV file containing recipient emails. The valid addresses in the file will receive this promotional email.</p>

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
                <span style="font-size:0.85rem;color:var(--muted);">One email per line or comma/semicolon/tab-separated values. The CSV file must include valid email addresses.</span>
            </label>

            <p style="margin:0;color:var(--muted);font-size:0.9rem;">Valid addresses will be extracted from the uploaded file and used for sending.</p>

            <button class="btn btn-primary" type="submit" style="justify-self:start;">Send Notification</button>
        </form>
    </div>
@endsection
