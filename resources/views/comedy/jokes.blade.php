@extends('layouts.app')

@section('title', 'My Jokes')

@section('css')
<link rel="stylesheet" href="{{ asset('custom/css/dashboard.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="templates-page">
    <div class="templates-header">
        <h1>My Jokes</h1>
        <p>Browse and manage all your generated jokes</p>
    </div>

    <div class="table-container">
        <table id="jokesTable" class="jokes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Joke</th>
                    <th>Category</th>
                    <th>Template</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jokes as $joke)
                <tr>
                    <td>{{ $joke->id }}</td>
                    <td class="joke-cell">{{ Str::limit($joke->generated_joke, 100) }}</td>
                    <td>{{ $joke->template->category->name ?? 'N/A' }}</td>
                    <td>{{ $joke->template->name ?? 'N/A' }}</td>
                    <td>{{ $joke->created_at->format('M d, Y') }}</td>
                    <td>
                        <button class="action-btn view-joke" data-joke="{{ htmlspecialchars($joke->generated_joke) }}" data-id="{{ $joke->id }}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn copy-joke" data-joke="{{ htmlspecialchars($joke->generated_joke) }}">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button class="action-btn delete-joke" data-id="{{ $joke->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- View Joke Modal -->
<div class="modal" id="viewJokeModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Joke Details</h5>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <textarea id="viewJokeText" rows="6" readonly class="custom-joke-textarea"></textarea>
                <div class="modal-actions">
                    <button class="copy-modal-btn" id="copyModalBtn">
                        <i class="fas fa-copy"></i> Copy to Clipboard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#jokesTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ jokes per page",
                info: "Showing _START_ to _END_ of _TOTAL_ jokes",
                emptyTable: "No jokes found. Generate your first joke!"
            }
        });

        // View joke
        $('.view-joke').on('click', function() {
            const joke = $(this).data('joke');
            $('#viewJokeText').val(joke);
            $('#viewJokeModal').fadeIn(200);
        });

        // Copy joke
        $('.copy-joke').on('click', function() {
            const joke = $(this).data('joke');
            copyToClipboard(joke);
            
            Swal.fire({
                title: 'Copied!',
                text: 'Joke copied to clipboard',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#121212',
                color: '#ffffff'
            });
        });

        // Copy from modal
        $('#copyModalBtn').on('click', function() {
            const joke = $('#viewJokeText').val();
            copyToClipboard(joke);
            showToast('Joke copied!', 'success');
        });

        // Delete joke
        $('.delete-joke').on('click', function() {
            const jokeId = $(this).data('id');
            const row = $(this).closest('tr');
            
            Swal.fire({
                title: 'Delete this joke?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e65856',
                cancelButtonColor: '#888888',
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel',
                background: '#121212',
                color: '#ffffff'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/comedy/joke/${jokeId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                row.fadeOut(300, function() { $(this).remove(); });
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Joke has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: '#e65856',
                                    background: '#121212',
                                    color: '#ffffff',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Failed to delete joke.',
                                icon: 'error',
                                confirmButtonColor: '#e65856',
                                background: '#121212',
                                color: '#ffffff'
                            });
                        }
                    });
                }
            });
        });
    });

    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }

    function closeViewModal() {
        $('#viewJokeModal').fadeOut(200);
    }

    $(document).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            closeViewModal();
        }
    });
</script>
@endsection