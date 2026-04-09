@extends('layouts.app')

@section('title', 'Manage Users')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/admin.css') }}">
@endsection

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1>Users</h1>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                 <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Agency</th>
                    <th>Created</th>
                    <th>Actions</th>
                 </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </td>
                    <td>{{ $user->agency->name ?? 'None' }}</td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn-icon btn-delete" onclick="deleteUser({{ $user->id }})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $users->links() }}
    </div>
</div>
@endsection

@section('js')
<script>
function deleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.users.destroy', ':id') }}".replace(':id', userId),
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire('Deleted!', 'User has been deleted.', 'success');
                    location.reload();
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Delete failed', 'error');
                }
            });
        }
    });
}
</script>
@endsection