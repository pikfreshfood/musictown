@php
$title = 'Master Users';
@endphp

@extends('layouts.admin')
@section('title', 'Master Users')
@section('page-title', 'Master Users')

@section('content')
<div class="py-6 px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Master Users</h1>
        <button onclick="toggleCreateForm()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
            + New Master User
        </button>
    </div>

    {{-- Create Form --}}
    <div id="createForm" class="bg-gray-800 rounded-lg p-6 mb-6 hidden">
        <h2 class="text-lg font-semibold text-white mb-4">Create New Master User</h2>
        <form method="POST" action="{{ route('admin.master-users.create') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Full Name</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Username (for referral link)</label>
                    <input type="text" name="username" required pattern="[a-zA-Z0-9_]+" title="Letters, numbers, underscores only" class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1">YourSite.com/ref/<strong class="text-purple-400">username</strong></p>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Phone (optional)</label>
                    <input type="text" name="phone" class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Password (leave blank for auto-generated)</label>
                    <input type="text" name="password" minlength="6" class="w-full bg-gray-700 text-white rounded px-3 py-2 text-sm border border-gray-600 focus:border-purple-500 outline-none">
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">Create Master User</button>
        </form>
    </div>

    {{-- Users List --}}
    @if ($users->isEmpty())
        <div class="bg-gray-800 rounded-lg p-8 text-center">
            <p class="text-gray-400">No master users yet.</p>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-700 text-gray-300 text-left">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Referral Link</th>
                        <th class="px-4 py-3">Referrals</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php $refCount = \App\Models\User::where('referrer_id', $u->id)->count(); @endphp
                        <tr class="border-t border-gray-700 hover:bg-gray-750">
                            <td class="px-4 py-3 text-white">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-purple-400">{{ $u->username }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $u->email }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('referral.redirect', $u->username) }}" target="_blank" class="text-blue-400 hover:underline text-xs break-all">
                                    {{ route('referral.redirect', $u->username) }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-white">{{ $refCount }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.master-users.show', $u->id) }}" class="text-purple-400 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
function toggleCreateForm() {
    document.getElementById('createForm').classList.toggle('hidden');
}
</script>
@endsection
