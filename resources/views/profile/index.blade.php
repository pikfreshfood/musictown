<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your PulseWave profile – track your balance and listen to music.">
    <title>Profile - PulseWave</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('profile') }}" aria-label="PulseWave home">
            <span class="brand-mark">P</span>
            <span>PulseWave</span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-site-nav>
            <a href="{{ route('profile') }}">Dashboard</a>
            <a href="{{ route('profile.wallet') }}">Wallet</a>
            <a href="{{ route('profile.settings') }}">Settings</a>
            <a href="{{ route('profile.withdrawal') }}">Withdrawal</a>
            <a href="{{ route('profile.history') }}">History</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        </nav>
        <div class="auth-links">
            <span style="color:var(--gold);font-weight:700;">{{ $user->name }}</span>
        </div>
    </header>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>

    <main style="padding: 120px clamp(20px, 5vw, 72px) 40px;">
        {{-- Balance Card --}}
        <section style="max-width:800px;margin:0 auto 48px;">
            <div class="balance-card">
                <p class="balance-label">Available Balance</p>
                <p class="balance-amount" id="balance-display">₦{{ number_format($user->balance, 2) }}</p>
                <p class="balance-hint">Earn ₦100 every second while listening!</p>
                <a class="button withdraw-btn" href="{{ route('profile.withdrawal') }}">Withdraw Funds</a>
            </div>
        </section>

        {{-- Now Playing Bar --}}
        <section id="now-playing-bar" style="display:none;max-width:800px;margin:0 auto 32px;">
            <div class="now-playing-inner">
                <div class="wave-bars" aria-hidden="true" style="height:32px;">
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
                <button id="pause-btn" type="button" aria-label="Pause" style="background:transparent;border:none;color:white;cursor:pointer;padding:4px 8px;line-height:1;display:flex;align-items:center;">
                    <svg id="pause-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                </button>
                <div style="flex:1;">
                    <p class="now-playing-label">Now Playing</p>
                    <strong id="now-playing-title" style="font-size:1.1rem;"></strong>
                </div>
                <div style="text-align:right;">
                    <p style="margin:0;font-size:0.85rem;color:var(--muted);">Earned this session</p>
                    <strong id="session-earnings" style="color:var(--gold);font-size:1.2rem;">₦0</strong>
                </div>
            </div>
        </section>

        {{-- Song List --}}
        <section style="max-width:800px;margin:0 auto;">
            <div class="section-heading" style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <p class="eyebrow">Music Library</p>
                    <h2>Listen & earn.</h2>
                </div>
                <button id="play-all-btn" class="button" style="display:inline-flex;align-items:center;gap:6px;font-size:0.9rem;">
                    <span style="font-size:1.1rem;">&#9654;</span> Play All
                </button>
            </div>

            @if (session('error'))
                <p class="form-message error-message">{{ session('error') }}</p>
            @endif

            <div style="display:grid;gap:14px;">
                @foreach ($songs as $song)
                    @php
                        $mins = intdiv($song->duration, 60);
                        $secs = $song->duration % 60;
                        $alreadyPlayed = in_array($song->id, $listenedToday);
                    @endphp
                    <div class="song-card" data-song-id="{{ $song->id }}" data-duration="{{ $song->duration }}" data-audio-url="{{ $song->audio_url ? asset('storage/' . $song->audio_url) : '' }}" style="cursor:default;">
                        @if ($alreadyPlayed)
                            <div class="play-btn played" aria-label="Already played today">
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;font-size:1.1rem;">&#10003;</span>
                            </div>
                        @else
                            <button class="play-btn" data-song-id="{{ $song->id }}" data-title="{{ $song->title }}" data-artist="{{ $song->artist }}" aria-label="Play {{ $song->title }}">
                                <span style="position:absolute;left:17px;top:13px;border-bottom:8px solid transparent;border-left:12px solid white;border-top:8px solid transparent;"></span>
                            </button>
                        @endif
                        <span style="flex:1;">
                            <strong>{{ $song->title }}</strong>
                            <small class="song-artist">{{ $song->artist }}</small>
                        </span>
                        <small class="song-duration">{{ $mins }}:{{ str_pad($secs, 2, '0') }}</small>
                        @if ($alreadyPlayed)
                            <small class="played-tag">Played today</small>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($songs->hasPages())
                <div class="pagination-wrap">
                    {{ $songs->links('pagination::tailwind') }}
                </div>
            @endif
        </section>
    </main>

     <script>
        let activeInterval = null;
        let sessionEarnings = 0;
        let activeAudio = null;
        let isPaused = false;
        let currentSongId = null;
        let activePlayBtn = null;

        function pauseSvg() {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
        }
        function playSvg() {
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="8,5 19,12 8,19"/></svg>';
        }

        function setPauseBtnIcon(icon) {
            var btn = document.getElementById('pause-btn');
            var svgContainer = document.getElementById('pause-icon');
            if (!btn || !svgContainer) return;
            if (icon === 'play') {
                svgContainer.outerHTML = '<svg id="pause-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="8,5 19,12 8,19"/></svg>';
                btn.setAttribute('aria-label', 'Play');
            } else {
                svgContainer.outerHTML = '<svg id="pause-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
                btn.setAttribute('aria-label', 'Pause');
            }
        }

        function setPlayBtnIcon(btn, icon) {
            if (!btn) return;
            var sp = btn.querySelector('span');
            if (!sp) return;
            if (icon === 'play') {
                sp.style.cssText = 'position:absolute;left:17px;top:13px;border-bottom:8px solid transparent;border-left:12px solid white;border-top:8px solid transparent;';
                sp.innerHTML = '';
                btn.setAttribute('aria-label', btn.dataset.title ? 'Play ' + btn.dataset.title : 'Play');
            } else {
                sp.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;';
                sp.innerHTML = pauseSvg();
                btn.setAttribute('aria-label', btn.dataset.title ? 'Pause ' + btn.dataset.title : 'Pause');
            }
        }

        function stopAudio() {
            if (activeInterval) {
                clearInterval(activeInterval);
                activeInterval = null;
            }
            if (activeAudio) {
                activeAudio.onended = null;
                activeAudio.pause();
                activeAudio.src = '';
                activeAudio = null;
            }
            if (activePlayBtn) {
                setPlayBtnIcon(activePlayBtn, 'play');
                activePlayBtn.style.opacity = '';
                activePlayBtn = null;
            }
            currentSongId = null;
            isPaused = false;
        }

        function togglePause() {
            if (!activeAudio) return;

            if (!isPaused) {
                isPaused = true;
                activeAudio.pause();
                if (activeInterval) {
                    clearInterval(activeInterval);
                    activeInterval = null;
                }
                setPauseBtnIcon('play');
                if (activePlayBtn) setPlayBtnIcon(activePlayBtn, 'play');
            } else {
                isPaused = false;
                activeAudio.play().catch(function(e) {
                    console.error('Resume failed:', e.message || e);
                });
                if (currentSongId) {
                    activeInterval = setInterval(tickFn, 1000);
                }
                setPauseBtnIcon('pause');
                if (activePlayBtn) setPlayBtnIcon(activePlayBtn, 'pause');
            }
        }

        async function tickFn() {
            if (!currentSongId) return;
            try {
                const tickRes = await fetch('{{ url('music') }}/tick/' + currentSongId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });
                const tickData = await tickRes.json();
                if (tickRes.ok) {
                    sessionEarnings += 100;
                    document.getElementById('session-earnings').textContent = '₦' + sessionEarnings;
                    document.getElementById('balance-display').textContent = '₦' + parseFloat(tickData.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            } catch (e) {
                console.error('Tick failed', e);
            }
        }

        async function playSong(card) {
            const songId = card.dataset.songId;
            const audioUrl = card.dataset.audioUrl || '';
            const playBtn = card.querySelector('.play-btn:not(.played)');
            const title = playBtn?.dataset.title || 'Unknown';
            const artist = playBtn?.dataset.artist || 'Unknown';

            if (!songId || !audioUrl) return null;

            if (currentSongId === songId && activeAudio) {
                togglePause();
                return null;
            }

            stopAudio();
            isPaused = false;
            currentSongId = songId;

            setPauseBtnIcon('pause');

            let audio = null;
            if (audioUrl) {
                audio = new Audio(audioUrl);
                audio.loop = false;
            }
            activeAudio = audio;

            try {
                const res = await fetch('{{ url('music') }}/play/' + songId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await res.json();

                if (!res.ok) {
                    stopAudio();
                    alert(data.error || 'Cannot play this song.');
                    return null;
                }

                document.getElementById('now-playing-bar').style.display = 'block';
                document.getElementById('now-playing-title').textContent = title + ' - ' + artist;
                sessionEarnings = 0;
                document.getElementById('session-earnings').textContent = '₦0';

                if (playBtn) {
                    playBtn.style.opacity = '0.5';
                    activePlayBtn = playBtn;
                    setPlayBtnIcon(playBtn, 'pause');
                }

                if (audio) {
                    audio.play().then(function() {
                        console.log('Audio playback started successfully');
                    }).catch(function(e) {
                        console.error('Audio play failed:', e.message || e);
                    });
                }

                activeInterval = setInterval(tickFn, 1000);

                return new Promise(function(resolve) {
                    if (audio) {
                        audio.onended = function() {
                            clearInterval(activeInterval);
                            activeInterval = null;
                            stopAudio();
                            currentSongId = null;
                            resolve(true);
                        };
                    } else {
                        var fallbackDuration = parseInt(card.dataset.duration) || 30;
                        setTimeout(function() {
                            clearInterval(activeInterval);
                            activeInterval = null;
                            stopAudio();
                            currentSongId = null;
                            resolve(true);
                        }, fallbackDuration * 1000);
                    }
                });
            } catch (e) {
                console.error('Play failed', e);
                alert('Something went wrong. Please try again.');
                return null;
            }
        }

        document.addEventListener('visibilitychange', function() {
            if (document.hidden && activeAudio && !isPaused) {
                if (activeInterval) {
                    clearInterval(activeInterval);
                    activeInterval = null;
                }
                activeAudio.pause();
                isPaused = true;
                setPauseBtnIcon('play');
                if (activePlayBtn) setPlayBtnIcon(activePlayBtn, 'play');
            }
        });

        document.querySelectorAll('.play-btn:not(.played)').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const card = this.closest('.song-card');
                const result = await playSong(card);
                if (result) {
                    document.getElementById('now-playing-bar').style.display = 'none';
                    location.reload();
                }
            });
        });

        document.getElementById('pause-btn').addEventListener('click', togglePause);

        const playAllBtn = document.getElementById('play-all-btn');
        if (playAllBtn) {
            playAllBtn.addEventListener('click', async function() {
                if (activeInterval) return;
                const cards = document.querySelectorAll('.song-card');
                for (const card of cards) {
                    if (card.querySelector('.play-btn.played')) continue;
                    const result = await playSong(card);
                    if (result === null) break;
                }
                document.getElementById('now-playing-bar').style.display = 'none';
                location.reload();
            });
        }
    </script>

    <style>
        .balance-card {
            background: linear-gradient(145deg, rgba(12,24,48,0.88), rgba(4,9,18,0.94));
            border: 1px solid rgba(72,181,255,0.24);
            border-radius: 12px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(20,118,255,0.12);
            padding: clamp(24px, 4vw, 40px);
            text-align: center;
        }
        .balance-label {
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 8px;
        }
        .balance-amount {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin: 0;
            color: white;
        }
        .balance-hint {
            color: var(--orange);
            font-size: 0.85rem;
            font-weight: 700;
            margin: 12px 0 0;
        }
        .now-playing-inner {
            background: linear-gradient(145deg, rgba(20,118,255,0.12), rgba(255,122,26,0.08));
            border: 1px solid rgba(255,184,77,0.42);
            border-radius: 8px;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .now-playing-label {
            margin: 0;
            font-size: 0.8rem;
            color: var(--orange);
            font-weight: 800;
            text-transform: uppercase;
        }
        .play-btn {
            background: linear-gradient(135deg, var(--blue), var(--orange));
            box-shadow: 0 0 28px var(--glow-blue);
            border: none;
            border-radius: 50%;
            flex: 0 0 auto;
            height: 42px;
            width: 42px;
            position: relative;
            cursor: pointer;
            transition: transform 180ms ease, opacity 180ms ease;
        }
        .play-btn:hover {
            transform: scale(1.08);
        }
        .play-btn.played {
            background: var(--green);
            box-shadow: none;
            cursor: default;
            opacity: 0.5;
        }
        .song-artist {
            display: block;
            color: var(--muted);
            margin-top: 5px;
        }
        .song-duration {
            color: var(--muted);
            font-size: 0.8rem;
        }
        .played-tag {
            color: var(--green);
            font-size: 0.75rem;
            font-weight: 700;
        }
        #play-all-btn:disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .error-message {
            background: rgba(255, 50, 50, 0.12);
            border: 1px solid rgba(255, 50, 50, 0.4);
            color: #ff6b6b;
        }
        .withdraw-btn {
            display: inline-flex;
            margin-top: 16px;
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
            border-color: rgba(255, 122, 26, 0.6);
            color: var(--orange);
        }
        .pagination-wrap nav[role="navigation"] span[aria-current="page"] {
            background: linear-gradient(135deg, var(--blue), var(--orange));
            border-color: transparent;
            color: white;
        }
    </style>
</body>
</html>
