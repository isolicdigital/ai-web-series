@extends('layouts.app')

@section('title', 'Agency Dashboard - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-7xl">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent">
                    Agency Dashboard
                </h1>
                <p class="text-gray-400 text-sm mt-1">Manage your agency members and monitor performance</p>
            </div>
            <a href="{{ route('agency.members.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Member
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Members -->
            <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-xl p-5 flex items-center gap-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-purple-500 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-3xl font-bold text-white">{{ $totalMembers }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Total Members</div>
                </div>
            </div>

            <!-- Active Members -->
            <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-xl p-5 flex items-center gap-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-purple-500 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-3xl font-bold text-white">{{ $activeMembers }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Active Members</div>
                </div>
            </div>

            <!-- Total Sessions -->
            <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-xl p-5 flex items-center gap-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-purple-500 hover:shadow-lg hover:shadow-purple-500/10">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-3xl font-bold text-white">{{ $totalSessions }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Total Sessions</div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-xl p-5 mb-8">
            <form method="GET" action="{{ route('agency.members') }}">
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Input -->
                    <div class="flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="search" placeholder="Search by name or email..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2.5 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                    </div>
                    
                    <!-- Status Filter -->
                    <select name="status" class="px-4 py-2.5 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all cursor-pointer">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    
                    <!-- Buttons -->
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium text-sm transition-all duration-300">
                        Filter
                    </button>
                    <a href="{{ route('agency.members') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-medium text-sm transition-all duration-300 text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Members Table -->
        <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-purple-500/10 border-b border-purple-500/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Sessions</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-purple-500/10">
                        @forelse($members as $member)
                        <tr class="hover:bg-white/5 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $member->id }}</td>
                            <td class="px-6 py-4 text-sm text-white font-medium">{{ $member->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $member->email }}</td>
                            <td class="px-6 py-4">
                                @if($member->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $member->sessions_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $member->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('agency.members.edit', $member->id) }}" class="p-1.5 text-gray-400 hover:text-purple-500 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" onclick="deleteMember({{ $member->id }})" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                No members found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $members->links() }}
        </div>
        
    </div>
</div>
@endsection

@section('scripts')
<script>
function deleteMember(memberId) {
    if (confirm('Are you sure you want to remove this member from the agency?')) {
        fetch(`/agency/members/${memberId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to delete member. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection