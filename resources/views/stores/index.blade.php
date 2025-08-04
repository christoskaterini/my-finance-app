@extends('layouts.studio')
@section('page-title', __('Manage Stores'))

@section('content')
    <div class="d-flex justify-content-start">
        <div style="max-width: 960px; width: 100%;">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ __('All Stores') }}</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#storeModal" data-action="create">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('New Store') }}
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th> {{-- Column for the drag handle --}}
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Comments') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        {{-- Add the ID for SortableJS to target --}}
                        <tbody id="stores-list">
                            @forelse ($stores as $store)
                                {{-- Add the data-id attribute to the row --}}
                                <tr data-id="{{ $store->id }}">
                                    {{-- Add the drag handle icon --}}
                                    <td class="drag-handle" style="cursor: move; vertical-align: middle;"><i class="bi bi-grip-vertical"></i></td>
                                    <td style="vertical-align: middle;">{{ $store->name }}</td>
                                    <td style="vertical-align: middle;">{{ Str::limit($store->comments, 50) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary me-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#storeModal"
                                                data-action="edit"
                                                data-store="{{ json_encode($store) }}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>

                                        <form id="delete-form-{{ $store->id }}" action="{{ route('stores.destroy', $store->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-trigger-btn"
                                                    data-form-id="delete-form-{{ $store->id }}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">{{ __('No stores found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Add the "Save Order" button footer --}}
                <div class="card-footer text-end" id="save-order-footer" style="display: none;">
                    <button class="btn btn-success" id="save-order-btn">{{ __('Save New Order') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- The Create/Edit Modal (no changes needed here) -->
    <div class="modal fade" id="storeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="storeModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="storeForm" action="" method="POST">
                    @csrf
                    <div id="modal-method-field"></div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modalStoreName" class="form-label">{{ __('Store Name') }}</label>
                            <input type="text" class="form-control" name="name" id="modalStoreName" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalStoreComments" class="form-label">{{ __('Comments') }}</label>
                            <textarea class="form-control" name="comments" id="modalStoreComments" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Add all the necessary JavaScript for this page --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Logic for Create/Edit Modal
    const storeModal = document.getElementById('storeModal');
    if (storeModal) {
        const storeForm = document.getElementById('storeForm');
        const modalTitle = document.getElementById('storeModalLabel');
        const modalStoreName = document.getElementById('modalStoreName');
        const modalStoreComments = document.getElementById('modalStoreComments');
        const modalMethodField = document.getElementById('modal-method-field');

        storeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');

            if (action === 'create') {
                storeForm.action = '{{ route("stores.store") }}';
                modalMethodField.innerHTML = '';
                modalTitle.textContent = '{{ __("New Store") }}';
                storeForm.reset();
            } else if (action === 'edit') {
                const store = JSON.parse(button.getAttribute('data-store'));
                storeForm.action = `/stores/${store.id}`;
                modalMethodField.innerHTML = '@method("PUT")';
                modalTitle.textContent = '{{ __("Edit Store") }}';
                modalStoreName.value = store.name;
                modalStoreComments.value = store.comments;
            }
        });
    }

    // Logic for SortableJS Drag-and-Drop
    const saveOrderFooter = document.getElementById('save-order-footer');
    const saveOrderBtn = document.getElementById('save-order-btn');
    const tableBody = document.getElementById('stores-list');
    let hasOrderChanged = false;

    if (tableBody) {
        const sortable = new Sortable(tableBody, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: function () {
                hasOrderChanged = true;
                saveOrderFooter.style.display = 'block';
            },
        });

        if (saveOrderBtn) {
            saveOrderBtn.addEventListener('click', function() {
                if (!hasOrderChanged) return;

                const newOrder = sortable.toArray();

                fetch('{{ route("stores.updateOrder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: newOrder })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Show a success toast
                        const toastContainer = document.querySelector('.toast-container');
                        const toastHTML = `<div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">{{ __('Order saved successfully!') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`;
                        toastContainer.innerHTML = toastHTML;
                        new bootstrap.Toast(toastContainer.querySelector('.toast'), {delay: 3000}).show();

                        saveOrderFooter.style.display = 'none';
                        hasOrderChanged = false;
                    }
                }).catch(error => console.error('Error:', error));
            });
        }
    }
});
</script>
@endpush
