<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'PulseWave') }} Notification</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;background:#f6f8fb;color:#111;line-height:1.6;">
    <div style="max-width:680px;margin:0 auto;padding:24px;background:white;border:1px solid #e4e7ec;border-radius:12px;">
        <h1 style="margin:0 0 20px;font-size:24px;color:#111;">{{ $subject ?? 'New update from ' . config('app.name', 'PulseWave') }}</h1>
        <div style="font-size:15px;color:#333;white-space:pre-wrap;">{!! nl2br(e($messageContent)) !!}</div>
        <p style="margin-top:28px;font-size:14px;color:#6b7280;">If you no longer wish to receive promotional emails, please reply with "unsubscribe".</p>
    </div>
</body>
</html>
