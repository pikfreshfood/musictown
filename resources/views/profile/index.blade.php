@extends('layouts.user')

@section('title', 'Profile')
@section('page-title', 'Dashboard')
@section('meta-description', 'Your Music Town profile – track your balance and listen to music.')

@section('content')

        {{-- Balance Card --}}
        <section style="max-width:800px;margin:0 auto 48px;">
            <div class="balance-card">
                <span class="tier-badge-dash">{{ str_replace('tier', 'Tier ', $user->tier) }}</span>
                <p class="balance-label">Available Balance</p>
                <p class="balance-amount" id="balance-display">₦{{ number_format($user->balance, 2) }}</p>
                <p class="balance-hint">Earn ₦5 while listening!</p>
                <a class="button withdraw-btn" href="{{ route('profile.withdrawal') }}">Withdraw Funds</a>
                <p style="margin:16px 0 4px;font-size:0.8rem;color:var(--blue-soft);font-weight:700;">Your Referral Link</p>
                <div class="ref-link-dash">
                    <span class="ref-link-text">{{ route('signup', ['ref' => $user->referral_code]) }}</span>
                    <button class="ref-copy-btn" onclick="copyDashRefLink()" aria-label="Copy referral link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>
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
                    <h2 style="font-size:1.1rem;">Listen & earn.</h2>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <div style="position:relative;">
                        <form method="GET" action="{{ route('profile') }}" style="display:flex;gap:6px;" id="search-form">
                            <input type="text" name="q" id="search-input" value="{{ $q ?? '' }}" placeholder="Search songs or artists..." class="input-field" style="min-height:auto;padding:6px 10px;font-size:0.85rem;width:180px;" autocomplete="off">
                            <button type="submit" class="button" style="font-size:0.85rem;padding:6px 12px;">Search</button>
                            @if (!empty($q))
                                <a href="{{ route('profile') }}" class="button" style="font-size:0.85rem;padding:6px 12px;background:transparent;border:1px solid var(--line);">Clear</a>
                            @endif
                        </form>
                        <div id="autocomplete-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:#0a1428;border:1px solid rgba(59,130,246,0.3);border-radius:8px;margin-top:4px;z-index:50;overflow:hidden;"></div>
                    </div>
                    <button id="play-all-btn" class="button" style="display:inline-flex;align-items:center;gap:6px;font-size:0.9rem;">
                        <span style="font-size:1.1rem;">&#9654;</span> Play All
                    </button>
                </div>
            </div>

            @if (session('error'))
                <p class="form-message error-message">{{ session('error') }}</p>
            @endif

            <div id="song-list" style="display:grid;gap:8px;">
                @forelse ($songs as $song)
                    @php
                        $mins = intdiv($song->duration, 60);
                        $secs = $song->duration % 60;
                        $onCooldown = in_array($song->id, $listenedRecent);
                    @endphp
                    @php $audioUrl = $song->audio_url ? (str_starts_with($song->audio_url, 'http') ? $song->audio_url : asset('storage/' . $song->audio_url)) : ''; @endphp
                    <div class="song-card" data-song-id="{{ $song->id }}" data-duration="{{ $song->duration }}" data-audio-url="{{ $audioUrl }}">
                        @if ($onCooldown)
                            <div class="play-btn played" aria-label="On cooldown">
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:white;font-size:0.9rem;">&#9202;</span>
                            </div>
                        @else
                            <button class="play-btn" data-song-id="{{ $song->id }}" data-title="{{ $song->title }}" data-artist="{{ $song->artist }}" aria-label="Play {{ $song->title }}">
                                <span style="position:absolute;left:15px;top:11px;border-bottom:7px solid transparent;border-left:10px solid white;border-top:7px solid transparent;"></span>
                            </button>
                        @endif
                        <span class="song-info">
                            <strong class="song-title">{{ $song->title }}</strong>
                            @if ($song->image_url)
                                <img src="{{ $song->image_url }}" alt="" class="song-thumb">
                            @endif
                            <small class="song-artist">{{ $song->artist }}</small>
                        </span>
                        <small class="song-duration">{{ $mins }}:{{ str_pad($secs, 2, '0') }}</small>
                        @if ($onCooldown)
                            <small class="played-tag">10 min</small>
                        @endif
                        @if ($song->audio_url)
                            <a href="{{ route('music.download', $song->id) }}" class="download-btn" title="Download">&#8595;</a>
                        @endif
                    </div>
                @empty
                    <p style="text-align:center;padding:24px;color:var(--muted);font-size:0.9rem;">No songs found. Try a different search.</p>
                @endforelse
            </div>

            <div id="scroll-sentinel" style="height:1px;"></div>
            <div id="scroll-loading" style="display:none;text-align:center;padding:16px;color:var(--muted);font-size:0.85rem;">Loading more songs...</div>
        </section>
    </div>

@endsection

 @push('scripts')
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
                    sessionEarnings += 5;
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



        function copyDashRefLink() {
            const text = document.querySelector('.ref-link-text')?.textContent?.trim();
            if (!text) return;
            navigator.clipboard.writeText(text).then(function() {
                const btn = document.querySelector('.ref-copy-btn');
                const orig = btn.innerHTML;
                btn.innerHTML = '<span style="font-size:0.75rem;">Copied!</span>';
                setTimeout(function() { btn.innerHTML = orig; }, 2000);
            }).catch(function() {
                alert('Press Ctrl+C to copy');
            });
        }

        // ── Infinite Scroll ──
        (function() {
            var sentinel = document.getElementById('scroll-sentinel');
            var loading = document.getElementById('scroll-loading');
            var list = document.getElementById('song-list');
            var page = 2;
            var loadingMore = false;
            var hasMore = {{ $songs->hasPages() ? 'true' : 'false' }};
            var searchQ = '{{ $q ?? '' }}';

            if (!sentinel || !list) return;

            if (!hasMore) {
                sentinel.style.display = 'none';
                return;
            }

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && hasMore && !loadingMore) {
                        loadingMore = true;
                        loading.style.display = 'block';

                        var url = '{{ route('music.load-more') }}?page=' + page;
                        if (searchQ) url += '&q=' + encodeURIComponent(searchQ);

                        fetch(url)
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.html) {
                                    list.insertAdjacentHTML('beforeend', data.html);
                                    attachPlayButtons();
                                }
                                hasMore = data.hasMore;
                                page = data.nextPage;
                                loadingMore = false;
                                loading.style.display = 'none';
                                if (!hasMore) {
                                    observer.unobserve(sentinel);
                                    sentinel.style.display = 'none';
                                }
                            })
                            .catch(function() {
                                loadingMore = false;
                                loading.style.display = 'none';
                            });
                    }
                });
            }, { rootMargin: '200px' });

            observer.observe(sentinel);

            function attachPlayButtons() {
                list.querySelectorAll('.song-card .play-btn:not(.played)').forEach(function(btn) {
                    if (btn._attached) return;
                    btn._attached = true;
                    btn.addEventListener('click', async function() {
                        var card = this.closest('.song-card');
                        var result = await playSong(card);
                        if (result) {
                            document.getElementById('now-playing-bar').style.display = 'none';
                            location.reload();
                        }
                    });
                });
            }

            attachPlayButtons();
        })();

        // ── Autocomplete Search ──
        (function() {
            var input = document.getElementById('search-input');
            var results = document.getElementById('autocomplete-results');
            var form = document.getElementById('search-form');
            var timer = null;

            if (!input || !results) return;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 1) {
                    results.style.display = 'none';
                    results.innerHTML = '';
                    return;
                }
                timer = setTimeout(function() {
                    fetch('{{ route('music.search') }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (!data || !data.length) {
                                results.style.display = 'none';
                                results.innerHTML = '';
                                return;
                            }
                            var html = '';
                            data.forEach(function(song) {
                                var m = Math.floor(song.duration / 60);
                                var s = song.duration % 60;
                                var time = m + ':' + (s < 10 ? '0' : '') + s;
                                html += '<a href="{{ route('profile') }}?q=' + encodeURIComponent(q) + '" style="display:flex;align-items:center;gap:10px;padding:8px 12px;color:#dce7f8;text-decoration:none;border-bottom:1px solid rgba(59,130,246,0.1);transition:background 150ms;">';
                                if (song.image_url) {
                                    html += '<img src="' + song.image_url + '" alt="" style="width:28px;height:28px;border-radius:4px;object-fit:cover;">';
                                }
                                html += '<div style="flex:1;min-width:0;"><div style="font-weight:600;font-size:0.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + song.title + '</div><div style="font-size:0.75rem;color:var(--muted);">' + song.artist + '</div></div>';
                                html += '<span style="font-size:0.75rem;color:var(--muted);flex-shrink:0;">' + time + '</span>';
                                html += '</a>';
                            });
                            results.innerHTML = html;
                            results.style.display = 'block';
                            results.querySelectorAll('a').forEach(function(a) {
                                a.addEventListener('mouseenter', function() { this.style.background = 'rgba(59,130,246,0.12)'; });
                                a.addEventListener('mouseleave', function() { this.style.background = ''; });
                                a.addEventListener('click', function() { results.style.display = 'none'; });
                            });
                        })
                        .catch(function() { results.style.display = 'none'; });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !results.contains(e.target)) {
                    results.style.display = 'none';
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    results.style.display = 'none';
                }
            });
        })();

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
            border: 1px solid rgba(59,130,246,0.24);
            border-radius: 12px;
            box-shadow: 0 28px 90px rgba(0,0,0,0.46), 0 0 48px rgba(59,130,246,0.12);
            padding: clamp(24px, 4vw, 40px);
            text-align: center;
            position: relative;
        }
        .tier-badge-dash {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            color: var(--blue-soft);
            font-size: 0.85rem;
            font-weight: 700;
            margin: 12px 0 0;
        }
        .now-playing-inner {
            background: linear-gradient(145deg, rgba(59,130,246,0.12), rgba(147,197,253,0.08));
            border: 1px solid rgba(147,197,253,0.42);
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
            color: var(--blue-soft);
            font-weight: 800;
            text-transform: uppercase;
        }
        .song-card {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            background: linear-gradient(145deg, rgba(12,24,48,0.7), rgba(4,9,18,0.8));
            border: 1px solid rgba(59,130,246,0.12);
            border-radius: 8px;
            transition: border-color 180ms ease, background 180ms ease;
        }
        .song-card:hover {
            border-color: rgba(59,130,246,0.3);
            background: linear-gradient(145deg, rgba(12,24,48,0.85), rgba(4,9,18,0.9));
        }
        .play-btn {
            background: linear-gradient(135deg, var(--blue), var(--blue-soft));
            box-shadow: 0 0 16px var(--glow-blue);
            border: none;
            border-radius: 50%;
            flex: 0 0 auto;
            height: 32px;
            width: 32px;
            position: relative;
            cursor: pointer;
            transition: transform 180ms ease, opacity 180ms ease;
            padding: 0;
        }
        .play-btn:hover {
            transform: scale(1.08);
        }
        .play-btn.played {
            background: rgba(147,197,253,0.25);
            box-shadow: none;
            cursor: default;
            opacity: 0.5;
            height: 32px;
            width: 32px;
        }
        .song-info {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .song-title {
            font-size: 0.88rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .song-thumb {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            flex-shrink: 0;
        }
        .song-artist {
            color: var(--muted);
            font-size: 0.75rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex-shrink: 1;
        }
        .song-duration {
            color: var(--muted);
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .played-tag {
            color: var(--blue-soft);
            font-size: 0.7rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .download-btn {
            color: var(--blue-soft);
            text-decoration: none;
            padding: 2px 6px;
            font-size: 0.85rem;
            flex-shrink: 0;
            transition: color 180ms;
        }
        .download-btn:hover {
            color: white;
        }
        #play-all-btn:disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        @media (max-width: 600px) {
            .song-card { padding: 6px 8px; gap: 6px; }
            .play-btn { height: 28px; width: 28px; }
            .play-btn span { border-bottom-width: 6px !important; border-left-width: 8px !important; border-top-width: 6px !important; left: 13px !important; top: 10px !important; }
            .play-btn.played { height: 28px; width: 28px; }
            .play-btn.played span { font-size: 0.75rem; }
            .song-title { font-size: 0.82rem; }
            .song-artist { font-size: 0.7rem; }
            .song-duration { font-size: 0.7rem; }
            .played-tag { font-size: 0.65rem; }
            .download-btn { font-size: 0.8rem; padding: 2px 4px; }
        }
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .error-message {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #f87171;
        }
        .withdraw-btn {
            display: inline-flex;
            margin-top: 16px;
        }
        .ref-link-dash {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            background: rgba(2,6,14,0.6);
            border: 1px solid rgba(59,130,246,0.2);
            border-radius: 8px;
            padding: 8px 12px;
            max-width: 100%;
        }
        .ref-link-text {
            flex: 1;
            font-size: 0.8rem;
            color: var(--blue-soft);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .ref-copy-btn {
            background: transparent;
            border: 1px solid rgba(59,130,246,0.3);
            border-radius: 6px;
            color: var(--blue-soft);
            cursor: pointer;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            transition: border-color 180ms, color 180ms;
            flex-shrink: 0;
        }
        .ref-copy-btn:hover {
            border-color: var(--blue);
            color: white;
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
    </style>
@endpush
