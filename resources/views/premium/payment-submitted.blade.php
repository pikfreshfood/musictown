@extends('layouts.user')

@section('title', 'Payment Submitted')
@section('page-title', 'Payment')

@section('content')
        <section style="max-width:600px;margin:0 auto;">
            <div style="text-align:center;background:linear-gradient(145deg,rgba(59,130,246,0.08),rgba(59,130,246,0.04));border:1px solid rgba(59,130,246,0.35);border-radius:16px;padding:48px 24px;">
                <p style="font-size:3rem;margin:0 0 16px;">&#10003;</p>
                <h1 style="font-size:clamp(1.8rem,3vw,2.5rem);margin:0 0 12px;color:var(--blue-soft);">Payment Submitted</h1>
                <p style="color:var(--muted);font-size:1.1rem;margin:0 0 8px;">Your payment proof has been received.</p>
                <p style="color:var(--blue-soft);font-weight:700;font-size:1.1rem;margin:0 0 24px;display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;">
                    Contact admin for approval
                    @if ($telegramUsername)
                        <a href="https://t.me/{{ $telegramUsername }}" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;color:#0088cc;text-decoration:none;font-weight:700;padding:8px 16px;border-radius:8px;background:rgba(0,136,204,0.1);border:1px solid rgba(0,136,204,0.2);transition:background .2s;" onmouseover="this.style.background='rgba(0,136,204,0.2)'" onmouseout="this.style.background='rgba(0,136,204,0.1)'">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="#0088cc"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        </a>
                    @else
                        <span style="color:var(--muted);font-size:0.9rem;">No admin contact available</span>
                    @endif
                </p>
                <a href="{{ route('premium.index') }}" class="button" style="display:inline-flex;">Back to dashboard</a>
            </div>
        </section>

    <style>
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            padding: 12px 28px;
            text-decoration: none;
        }
    </style>
@endsection
