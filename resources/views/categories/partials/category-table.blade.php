<table class="table table-hover">
    <thead>
        <tr>
            <th style="width: 50px;"></th> {{-- Column for the drag handle --}}
            <th>{{ __('Name') }}</th>
            <th class="text-end">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody id="{{ $tableId }}">
        @forelse ($categories as $category)
            <tr data-id="{{ $category->id }}">
                <td class="drag-handle" style="cursor: move; vertical-align: middle;">
                    <i class="bi bi-grip-vertical"></i>
                </td>
                <td style="vertical-align: middle;">
                    {{ $category->name }}
                </td>
                <td class="text-end">
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-outline-secondary me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#categoryModal"
                            data-action="edit"
                            data-category="{{ json_encode($category) }}">
                        <i class="bi bi-pencil-fill"></i>
                    </button>

                    <!-- Delete Button/Form -->
                    <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger delete-trigger-btn"
                                data-form-id="delete-form-{{ $category->id }}">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">{{ __('No categories found.') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
