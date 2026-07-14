@php
$title = 'Master User: ' . $user->name;
@endphp

@extends('layouts.admin')
@section('title', 'Master User: ' . $user->name)
@section('page-title', 'Master User: ' . $user->name)

@section('content')
<div class="py-6 px-4">
    <a href="{{ route('admin.master-users') }}" class="text-purple-400 hover:underline text-sm mb-4 inline-block">&larr; Back to Master Users</a>

    {{-- User Info --}}
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                <p class="text-gray-400 mt-1">{{ $user->email }}</p>
                @if ($user->phone)
                    <p class="text-gray-400 text-sm">Phone: {{ $user->phone }}</p>
                @endif
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-700 rounded-lg p-4 text-center">
                <p class="text-3xl font-bold text-purple-400">{{ $referralCount }}</p>
                <p class="text-gray-400 text-sm mt-1">Total Referrals</p>
            </div>
            <div class="bg-gray-700 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Username</p>
                <p class="text-white font-mono mt-1">{{ $user->username }}</p>
            </div>
            <div class="bg-gray-700 rounded-lg p-4">
                <p class="text-gray-400 text-sm">Referral Code</p>
                <p class="text-white font-mono mt-1">{{ $user->referral_code }}</p>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-gray-400 text-sm mb-1">Referral Link</p>
            <div class="flex">
                <input type="text" readonly value="{{ $referralUrl }}" class="flex-1 bg-gray-700 text-white rounded-l px-3 py-2 text-sm border border-gray-600 outline-none" onclick="this.select()">
                <button onclick="copyLink()" class="px-4 py-2 bg-purple-600 text-white rounded-r hover:bg-purple-700 transition text-sm">Copy</button>
            </div>
        </div>
    </div>

    {{-- Referrals --}}
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Referred Users</h2>

        @if ($referrals->isEmpty())
            <p class="text-gray-400 text-center py-8">No referrals yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-700 text-gray-300 text-left">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Signed Up</th>
                            <th class="px-4 py-3">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($referrals as $ref)
                            <tr class="border-t border-gray-700 hover:bg-gray-750">
                                <td class="px-4 py-3 text-white">{{ $ref->name }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $ref->email }}</td>
                                <td class="px-4 py-3 text-gray-400">{{ $ref->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-green-400">₦{{ number_format($ref->balance) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function copyLink() {
    const input = document.querySelector('input[type="text"]');
    input.select();
    document.execCommand('copy');
    alert('Link copied!');
}
</script>
@endsection
