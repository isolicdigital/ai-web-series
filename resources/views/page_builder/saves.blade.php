@extends('layouts.app')

@section('title', 'DFY Pages')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dfy.css') }}">
<style>
    /* Simple floating create button */
    .create-btn-wrapper {
        text-align: right;
        margin-bottom: 1.5rem;
    }
    
    .create-btn-wrapper .btn-primary-action {
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="pagebuilder-container">
    <div class="pagebuilder-header">
        <h1 class="pagebuilder-title">{{ $page_title ?? 'My Pages' }}</h1>
        <p class="pagebuilder-subtitle">Manage and edit your DFY pages</p>
    </div>

    <!-- Single Create Button - No ugly bar -->
    <div class="create-btn-wrapper">
        <a href="{{ route('page-builder.dfy') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Create New Page
        </a>
    </div>

    @if ($saves->count())
    <div class="pagebuilder-table-container">
        <table class="pagebuilder-table">
            <thead>
                <tr>
                    <th><i class="fas fa-heading"></i> Title</th>
                    <th><i class="fas fa-calendar-alt"></i> Created</th>
                    <th><i class="fas fa-cog"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saves as $save)
                <tr>
                    <td>
                        <div class="page-title">{{ $save->title }}</div>
                        @if($save->slug)
                        <div class="page-slug">
                            <i class="fas fa-link"></i> {{ Str::limit($save->slug, 35) }}
                        </div>
                        @endif
                    </td>
                    <td>
                        <span class="page-date">
                            <i class="fas fa-calendar-day"></i> {{ $save->created_at->format('d M, Y') }}
                        </span>
                        <div class="page-time">
                            <i class="fas fa-clock"></i> {{ $save->created_at->format('h:i A') }}
                        </div>
                    </td>
                    <td>
                        <div class="pagebuilder-actions-buttons">
                            <a href="{{ route('page-builder.show', ['id' => $save->slug, 'title' => base64_encode($save->title)]) }}" 
                               class="pagebuilder-icon pagebuilder-icon-edit" 
                               title="Edit" target="_blank">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ url('/p/?v='.base64_encode($save->slug)) }}" 
                               class="pagebuilder-icon pagebuilder-icon-view" 
                               title="View" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('page-builder.download', $save->id) }}" 
                               class="pagebuilder-icon pagebuilder-icon-download" 
                               title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" 
                                    class="pagebuilder-icon pagebuilder-icon-delete" 
                                    onclick="confirmDelete({{ $save->id }}, '{{ addslashes($save->title) }}')" 
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form id="delete-form-{{ $save->id }}" 
                                  action="{{ route('page-builder.delete', $save->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(method_exists($saves, 'links') && $saves->hasPages())
    <div class="pagination">
        {{ $saves->links() }}
    </div>
    @endif

    @else
    <div class="pagebuilder-empty">
        <i class="fas fa-file-alt"></i>
        <h4>No Pages Yet</h4>
        <p>Create your first DFY page to get started</p>
    </div>
    @endif
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(pageId, pageTitle) {
    Swal.fire({
        title: 'Delete Page?',
        html: `Are you sure you want to delete "<strong>${pageTitle}</strong>"?<br><span style="color: #a0a0a0;">This action cannot be undone.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        background: '#121212',
        color: '#ffffff'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                background: '#121212',
                color: '#ffffff',
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            document.getElementById(`delete-form-${pageId}`).submit();
        }
    });
}
</script>
@endsection