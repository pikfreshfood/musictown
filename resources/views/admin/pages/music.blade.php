@extends('layouts.admin')
@section('title', 'Music')
@section('page-title', 'Music Management')

@section('content')
    <div class="admin-card" style="margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <p style="font-weight:700;margin:0 0 12px;font-size:1rem;">Upload Music</p>
                <form method="POST" action="{{ route('admin.upload.music') }}" enctype="multipart/form-data" style="display:grid;gap:14px;">
                    @csrf
                    <label class="form-label">
                        Select music files (mp3, wav, ogg, aac, m4a, flac, wma)
                        <input type="file" name="music_files[]" multiple accept="audio/*" required class="input-field" style="padding-top:10px;min-height:auto;">
                    </label>
                    <p style="color:var(--muted);font-size:0.8rem;margin:0;">Title is auto-extracted from filename. Max 128MB per file.</p>
                    <button class="btn btn-primary" type="submit" style="justify-self:start;">Upload Music</button>
                </form>
            </div>

            <div style="display:flex;align-items:center;gap:10px;">
                <form method="POST" action="{{ route('admin.music.sync-jamendo') }}" style="display:flex;align-items:center;gap:8px;">
                    @csrf
                    <select name="tag" class="input-field" style="min-height:auto;padding:6px 8px;font-size:0.85rem;width:auto;">
                        <option value="all">All Genres</option>
                        @foreach (App\Services\JamendoService::getGenres() as $val => $label)
                            @if ($val !== 'all')
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="number" name="limit" value="50" min="1" max="200" class="input-field" style="min-height:auto;padding:6px 8px;font-size:0.85rem;width:70px;" title="Number of tracks">
                    <button type="submit" class="btn btn-primary" style="font-size:0.85rem;padding:6px 14px;">Sync from Jamendo</button>
                </form>
                <form method="POST" action="{{ route('admin.music.sync-audius') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="font-size:0.85rem;padding:6px 14px;">Sync from Audius (Nigeria)</button>
                </form>
                <form method="POST" action="{{ route('admin.music.delete-all') }}" onsubmit="return confirm('Delete all music and their files?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete All Music</button>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
            <p style="font-weight:700;margin:0;font-size:1rem;">All Songs ({{ $songs->total() }})</p>
            <form method="GET" action="{{ route('admin.music') }}" style="display:flex;gap:6px;">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search songs or artists..." class="input-field" style="min-height:auto;padding:6px 10px;font-size:0.85rem;width:180px;">
                <button type="submit" class="btn btn-primary" style="font-size:0.85rem;padding:6px 14px;">Search</button>
                @if (!empty($q))
                    <a href="{{ route('admin.music') }}" class="btn" style="font-size:0.85rem;padding:6px 14px;">Clear</a>
                @endif
            </form>
        </div>

        <div id="admin-song-list" style="display:grid;gap:6px;">
            @forelse ($songs as $song)
                @php $mins = intdiv($song->duration, 60); $secs = $song->duration % 60; @endphp
                <div class="admin-song-card">
                    <span class="admin-song-info">
                        <strong>{{ $song->title }}</strong>
                        <small>{{ $song->artist }}</small>
                    </span>
                    <small style="color:var(--muted);font-size:0.8rem;">{{ $mins }}:{{ str_pad($secs, 2, '0') }}</small>
                    <span style="color:var(--green);font-size:0.8rem;font-weight:700;">{{ $song->audio_url ? 'Yes' : 'No' }}</span>
                    <a class="btn btn-danger btn-sm" href="{{ route('admin.music.delete', $song->id) }}" onclick="return confirm('Delete {{ $song->title }}?')">Delete</a>
                </div>
            @empty
                <p style="text-align:center;padding:24px;color:var(--muted);font-size:0.9rem;">No songs found.</p>
            @endforelse
        </div>

        <div id="admin-scroll-sentinel" style="height:1px;"></div>
        <div id="admin-scroll-loading" style="display:none;text-align:center;padding:16px;color:var(--muted);font-size:0.85rem;">Loading more songs...</div>
    </div>

    @push('scripts')
    <style>
        .admin-song-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            background: linear-gradient(145deg, rgba(12,24,48,0.6), rgba(4,9,18,0.7));
            border: 1px solid var(--line);
            border-radius: 6px;
            font-size: 0.88rem;
            transition: border-color 180ms ease;
        }
        .admin-song-card:hover {
            border-color: rgba(59,130,246,0.3);
        }
        .admin-song-info {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .admin-song-info strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .admin-song-info small {
            color: var(--muted);
            font-size: 0.8rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex-shrink: 1;
        }
        .btn-sm {
            padding: 4px 10px;
            font-size: 0.78rem;
        }
        @media (max-width: 600px) {
            .admin-song-card { padding: 6px 8px; gap: 8px; font-size: 0.82rem; }
            .admin-song-info { gap: 4px; }
            .admin-song-info small { display: none; }
        }
    </style>
    <script>
        (function() {
            var sentinel = document.getElementById('admin-scroll-sentinel');
            var loading = document.getElementById('admin-scroll-loading');
            var list = document.getElementById('admin-song-list');
            var page = 2;
            var loadingMore = false;
            var hasMore = {{ $songs->hasPages() ? 'true' : 'false' }};
            var searchQ = '{{ $q ?? '' }}';

            if (!sentinel || !list || !hasMore) return;

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && hasMore && !loadingMore) {
                        loadingMore = true;
                        loading.style.display = 'block';

                        var url = '{{ route('admin.music.load-more') }}?page=' + page;
                        if (searchQ) url += '&q=' + encodeURIComponent(searchQ);

                        fetch(url)
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.html) {
                                    list.insertAdjacentHTML('beforeend', data.html);
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
        })();
    </script>
    @endpush
@endsection
