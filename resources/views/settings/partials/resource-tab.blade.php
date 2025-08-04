<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title }}</h5>
        <div>
            <button class="btn btn-primary btn-sm" data-action="create" data-form-config='{"resourceName": "{{$resourceName}}", "baseRouteName": "{{$baseRouteName}}", "fields": @json($fields), "columns": @json($columns)}'>
                <i class="bi bi-plus-lg"></i> {{__('New')}}
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover responsive-card-table table-fixed mb-0">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    @foreach($columns as $column)
                        <th class="w-25">{{ __($column) }}</th>
                    @endforeach
                    {{-- NEW: Conditionally add the Assigned Stores column --}}
                    @if($resourceName !== 'Store')
                        <th class="w-50">{{ __('Assigned Stores') }}</th>
                    @endif
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody id="{{ $tableId }}" class="sortable-table" data-update-order-route="{{ route($baseRouteName . '.updateOrder') }}">
                @forelse ($resources as $resource)
                    <tr data-id="{{ $resource->id }}">
                        <td class="drag-handle" data-label="" style="cursor: move; vertical-align: middle;"><i class="bi bi-grip-vertical"></i></td>
                        @foreach($fields as $key => $field)
                            <td data-label="{{ __($columns[$key]) }}" style="vertical-align: middle;">{{ Str::limit($resource->$field, 50) }}</td>
                        @endforeach

                        {{-- NEW: Display the assigned stores --}}
                        @if($resourceName !== 'Store')
                            <td data-label="{{ __('Assigned Stores') }}" style="vertical-align: middle;">
                                @if($resource->stores->isNotEmpty())
                                    {{-- Create a comma-separated list of store names --}}
                                    {{ $resource->stores->pluck('name')->join(', ') }}
                                @else
                                    <span class="text-muted">{{__('None')}}</span>
                                @endif
                            </td>
                        @endif

                        <td data-label="{{ __('Actions') }}">
                            <div class="d-flex justify-content-end flex-wrap">
                                <button class="btn btn-sm btn-outline-secondary me-2 mb-1" data-action="edit" data-resource='{{ json_encode($resource) }}' data-form-config='{"resourceName": "{{$resourceName}}", "baseRouteName": "{{$baseRouteName}}", "fields": @json($fields), "columns": @json($columns)}'>
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <form id="delete-form-{{$resourceName}}-{{ $resource->id }}" action="{{ route($baseRouteName . '.destroy', $resource->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-trigger-btn mb-1" data-form-id="delete-form-{{$resourceName}}-{{ $resource->id }}"><i class="bi bi-trash-fill"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + ($resourceName !== 'Store' ? 2 : 1) }}" class="text-center">{{ __('No items found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
