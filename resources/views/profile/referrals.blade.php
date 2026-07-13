@extends('layouts.user')

@section('title', 'Referrals')
@section('page-title', 'Referrals')
@section('meta-description', 'Your Music Town referral link and referred users.')

@section('content')
        <section style="max-width:800px;margin:0 auto;">
            <div class="section-heading">
                <p class="eyebrow">Refer & Earn</p>
                <h2 style="font-size:1.1rem;">Your Referrals</h2>
            </div>

            <div class="referral-link-card">
                <p style="margin:0 0 8px;font-weight:700;color:var(--blue-soft);font-size:0.9rem;">Your Referral Link</p>
                <div class="ref-link-row">
                    <input id="ref-link-input" type="text" value="{{ route('signup', ['ref' => $user->referral_code]) }}" readonly>
                    <button id="ref-copy-btn" class="button" onclick="copyRefLink()">Copy</button>
                </div>
            </div>

            @if ($referrals->isEmpty())
                <div class="empty-state">
                    <p>You haven't referred anyone yet. Share your link and earn when they join!</p>
                </div>
            @else
                <div style="display:grid;gap:12px;margin-top:24px;">
                    @foreach ($referrals as $ref)
                        <div class="referral-row">
                            <div>
                                <span style="font-weight:600;">{{ $ref->name }}</span>
                                <small style="display:block;color:var(--muted);margin-top:2px;">{{ $ref->email }}</small>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span class="tier-badge tier-{{ $ref->tier }}">{{ str_replace('tier', 'Tier ', $ref->tier) }}</span>
                                <small style="color:var(--muted);flex-shrink:0;">{{ $ref->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($referrals->hasPages())
                    <div class="pagination-wrap">
                        {{ $referrals->links('pagination::tailwind') }}
                    </div>
                @endif
            @endif
        </section>

    <script>
        function copyRefLink() {
            const input = document.getElementById('ref-link-input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById('ref-copy-btn');
                const orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = orig, 2000);
            }).catch(() => {
                alert('Press Ctrl+C to copy');
            });
        }
    </script>

    <style>
        .referral-link-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .ref-link-row {
            display: flex;
            gap: 8px;
        }
        .ref-link-row input {
            flex: 1;
            background: rgba(2,6,14,0.82);
            border: 1px solid rgba(59,130,246,0.25);
            border-radius: 8px;
            color: white;
            padding: 0 16px;
            font-size: 0.9rem;
            min-height: 48px;
            outline: 0;
        }
        .ref-link-row .button {
            border: 0;
            cursor: pointer;
            white-space: nowrap;
        }
        .empty-state {
            background: rgba(12,24,48,0.6);
            border: 1px solid rgba(59,130,246,0.15);
            border-radius: 10px;
            padding: 48px 24px;
            text-align: center;
            color: var(--muted);
        }
        .referral-row {
            background: rgba(12,24,48,0.6);
            border: 1px solid rgba(59,130,246,0.12);
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pagination-wrap {
            margin-top: 32px;
            display: flex;
            justify-content: center;
        }
        .pagination-wrap nav[role="navigation"] > div:first-child {
            display: none;
        }
        .pagination-wrap nav[role="navigation"] > div:last-child {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .pagination-wrap nav[role="navigation"] a,
        .pagination-wrap nav[role="navigation"] span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 10px;
            background: linear-gradient(145deg, rgba(12,24,48,0.86), rgba(4,9,18,0.92));
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #dce7f8;
            font-size: 0.9rem;
            font-weight: 700;
            transition: border-color 180ms ease, color 180ms ease;
            text-decoration: none;
        }
        .pagination-wrap nav[role="navigation"] a:hover {
            border-color: rgba(59,130,246,0.6);
            color: var(--blue-soft);
        }
        .pagination-wrap nav[role="navigation"] span[aria-current="page"] {
            background: linear-gradient(135deg, var(--blue), var(--blue-soft));
            border-color: transparent;
            color: white;
        }
        .tier-badge {
            display:inline-block;padding:4px 10px;border-radius:20px;
            font-size:0.75rem;font-weight:700;white-space:nowrap;
        }
        .tier-tier0 { background:rgba(100,116,139,0.2); color:#94a3b8; border:1px solid rgba(100,116,139,0.3); }
        .tier-tier1 { background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); }
        .tier-tier2 { background:rgba(168,85,247,0.15); color:#a78bfa; border:1px solid rgba(168,85,247,0.3); }
        .tier-tier3 { background:rgba(251,191,36,0.15); color:#fbbf24; border:1px solid rgba(251,191,36,0.3); }
    </style>
@endsection
