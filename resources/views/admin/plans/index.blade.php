@extends('layouts.app')

@section('title', 'Manage Plans')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/admin.css') }}">
@endsection

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1>Plans</h1>
        <a href="{{ route('admin.plans.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Add Plan
        </a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                 <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Validity Days</th>
                    <th>LP ID</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                 </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                <tr>
                    <td>{{ $plan->id }}</td>
                    <td>{{ $plan->name }}</td>
                    <td><code>{{ $plan->slug }}</code></td>
                    <td>{{ number_format($plan->validity_days) }}</td>
                    <td>{{ $plan->lp_id ?? '-' }}</td>
                    <td>{{ $plan->order }}</td>
                    <td>
                        <span class="badge {{ $plan->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                    </td>
                    <td class="actions">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-delete" onclick="return confirm('Delete this plan?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $plans->links() }}
    </div>
</div>
@endsection