@extends('layouts.app')

@section('title', 'Edit Team Member - ' . config('app.name'))

@section('content')
<div class="min-h-screen bg-black py-[120px] px-4">
    <div class="container mx-auto max-w-3xl">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-white via-purple-400 to-pink-500 bg-clip-text text-transparent">
                    Edit Team Member
                </h1>
                <p class="text-gray-400 text-sm mt-1">Update member information and permissions</p>
            </div>
            <a href="{{ route('agency.members') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl font-medium text-sm transition-all duration-300 hover:scale-105 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Members
            </a>
        </div>

        <!-- Form Container -->
        <div class="bg-white/5 backdrop-blur-lg border border-purple-500/20 rounded-2xl p-6 md:p-8">
            <form method="POST" action="{{ route('agency.members.update', $member->id) }}">
                @csrf
                @method('PUT')

                <!-- Full Name -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all @error('name') border-red-500 ring-red-500/20 @enderror" 
                           value="{{ old('name', $member->name) }}" 
                           required>
                    @error('name') 
                        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all @error('email') border-red-500 ring-red-500/20 @enderror" 
                           value="{{ old('email', $member->email) }}" 
                           required>
                    @error('email') 
                        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        Status
                    </label>
                    <select name="status" 
                            class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all cursor-pointer">
                        <option value="active" {{ old('status', $member->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $member->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') 
                        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        New Password <span class="text-gray-500 text-xs font-normal">(leave blank to keep current)</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all @error('password') border-red-500 ring-red-500/20 @enderror">
                    @error('password') 
                        <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        Confirm New Password
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           class="w-full px-4 py-3 bg-black/50 border border-purple-500/20 rounded-xl text-white text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-purple-500/20">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Update Member
                    </button>
                    <a href="{{ route('agency.members') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-medium text-sm transition-all duration-300 hover:scale-105 text-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                </div>

                <!-- Info Note -->
                <div class="mt-6 p-4 bg-purple-500/5 rounded-xl border-l-4 border-purple-500">
                    <p class="text-xs text-gray-400">
                        <strong class="text-purple-400">Note:</strong> Changes will take effect immediately. The member will be notified via email if their password is changed.
                    </p>
                </div>
            </form>
        </div>
        
    </div>
</div>
@endsection