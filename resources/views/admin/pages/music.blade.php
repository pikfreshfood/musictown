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
                <form method="POST" action="{{ route('admin.music.delete-all') }}" onsubmit="return confirm('Delete all music and their files?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete All Music</button>
                </form>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Duration</th>
                    <th>Audio</th>
                    <th>Uploaded</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($songs as $song)
                    @php $mins = intdiv($song->duration, 60); $secs = $song->duration % 60; @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $song->title }}</td>
                        <td style="color:var(--muted);">{{ $song->artist }}</td>
                        <td style="color:var(--muted);">{{ $mins }}:{{ str_pad($secs, 2, '0') }}</td>
                        <td>@if ($song->audio_url) <span style="color:var(--green);font-size:0.8rem;font-weight:700;">Yes</span> @else <span style="color:var(--muted);font-size:0.8rem;">No</span> @endif</td>
                        <td style="color:var(--muted);font-size:0.85rem;">{{ $song->created_at->format('M d, Y') }}</td>
                        <td style="text-align:center;">
                            <a class="btn btn-danger btn-sm" href="{{ route('admin.music.delete', $song->id) }}" onclick="return confirm('Delete {{ $song->title }}?')">Delete</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--muted);">No songs uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($songs->hasPages())
        <div class="pagination-wrap">{{ $songs->links('pagination::tailwind') }}</div>
    @endif
@endsection
