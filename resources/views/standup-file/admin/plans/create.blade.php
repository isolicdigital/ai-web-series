@extends('layouts.app')

@section('title', 'Create Plan')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/admin.css') }}">
@endsection

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1>Create Plan</h1>
        <a href="{{ route('admin.plans.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Plans
        </a>
    </div>

    <div class="admin-form-container">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="admin-form">
            @csrf

            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required>
                <small>URL-friendly identifier (e.g., front-end, unlimited)</small>
                @error('slug') <span class="error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="order">Order</label>
                <input type="number" id="order" name="order" value="{{ old('order', 0) }}">
                <small>Lower numbers appear first</small>
            </div>

            <div class="form-group">
                <label for="validity_days">Validity Days</label>
                <input type="number" id="validity_days" name="validity_days" value="{{ old('validity_days', 365) }}">
                <small>Number of days subscription lasts</small>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="lp_id">LP ID</label>
                    <input type="text" id="lp_id" name="lp_id" value="{{ old('lp_id') }}">
                </div>

                <div class="form-group half">
                    <label for="wp_id">WP ID</label>
                    <input type="text" id="wp_id" name="wp_id" value="{{ old('wp_id') }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="zoo_id">Zoo ID</label>
                    <input type="text" id="zoo_id" name="zoo_id" value="{{ old('zoo_id') }}">
                </div>

                <div class="form-group half">
                    <label for="exp_id">Exp ID</label>
                    <input type="text" id="exp_id" name="exp_id" value="{{ old('exp_id') }}">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Plan</button>
                <a href="{{ route('admin.plans.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection