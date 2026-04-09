@extends('layouts.app')

@section('title', 'Edit User')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/admin.css') }}">
@endsection

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1>Edit User: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="admin-form-container">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
                @error('password') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="form-group">
                <label for="agency_id">Agency</label>
                <select id="agency_id" name="agency_id">
                    <option value="">None (Standalone)</option>
                    @foreach($agencies as $agency)
                    <option value="{{ $agency->id }}" {{ old('agency_id', $user->agency_id) == $agency->id ? 'selected' : '' }}>
                        {{ $agency->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="plan_id">Plan (for standalone users)</label>
                <select id="plan_id" name="plan_id">
                    <option value="">No Plan</option>
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ optional($user->subscriptions)->plan_id == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Update User</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection