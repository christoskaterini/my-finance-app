@extends('layouts.studio')
@section('page-title', __('Application Settings'))

@section('content')

<!-- The Success Toast -->
@if(session('success'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
    <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">{{ session('success') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

<!-- The Application Settings Tabs -->
<div class="row">
    {{-- All content will live inside this responsive column --}}
    <div class="col-xl-9 col-lg-10">

        @if ($errors->any())
        <div class="alert alert-danger mb-4">
            <h5 class="alert-heading">{{__('Errors Found:')}}</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($activeTab == 'stores')
        @include('settings.partials.resource-tab', ['title'=>__('Manage Stores'),'resourceName'=>'Store','baseRouteName'=>'settings.stores','resources'=>$stores,'tableId'=>'stores-list','columns'=>['Name','Comments'],'fields'=>['name','comments']])
        @elseif($activeTab == 'expense-categories')
        @include('settings.partials.resource-tab', ['title'=>__('Manage Expense Categories'),'resourceName'=>'Expense Category','baseRouteName'=>'settings.expense-categories','resources'=>$expenseCategories,'tableId'=>'expense-categories-list','columns'=>['Name'],'fields'=>['name']])
        @elseif($activeTab == 'shifts')
        @include('settings.partials.resource-tab', ['title'=>__('Manage Shifts'),'resourceName'=>'Shift','baseRouteName'=>'settings.shifts','resources'=>$shifts,'tableId'=>'shifts-list','columns'=>['Name'],'fields'=>['name']])
        @elseif($activeTab == 'sources')
        @include('settings.partials.resource-tab', ['title'=>__('Manage Sources'),'resourceName'=>'Source','baseRouteName'=>'settings.sources','resources'=>$sources,'tableId'=>'sources-list','columns'=>['Name'],'fields'=>['name']])
        @elseif($activeTab == 'payment-methods')
        @include('settings.partials.resource-tab', ['title'=>__('Manage Payment Methods'),'resourceName'=>'Payment Method','baseRouteName'=>'settings.payment-methods','resources'=>$paymentMethods,'tableId'=>'payment-methods-list','columns'=>['Name'],'fields'=>['name']])
        @elseif($activeTab == 'general')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('General Settings') }}</h5>
            </div>
            <div class="card-body">
                @include('settings.partials.general-tab')
            </div>
        </div>
        @endif
    </div>
</div>

<!-- The Resource Management Modal -->
<div class="modal fade" id="resourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resourceModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resourceForm" action="" method="POST">
                <div id="modal-method-field"></div>@csrf<div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- The New Logo Management Modal -->
<div class="modal fade" id="logoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Manage Application Logo')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="logoUploadForm" action="{{ route('settings.updateLogo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_app_logo" class="form-label">{{__('Upload New Logo')}}</label>
                        <input class="form-control" type="file" id="modal_app_logo" name="app_logo" required>
                        <div class="form-text">{{__('Square PNG, max 2MB.')}}</div>
                    </div>
                </form>

                @if(isset($settings['app_logo']))
                    <hr>
                    <p>{{__('Or, remove the current logo:')}}</p>
                    <form id="logoRemoveForm" action="{{ route('settings.removeLogo') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            {{ __('Remove Current Logo') }}
                        </button>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                <button type="submit" form="logoUploadForm" class="btn btn-primary">{{__('Save New Logo')}}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script id="stores-data" data-stores="{{ json_encode($stores) }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // A list of all your stores, passed from the controller, for use in the modal
        const storesDataElement = document.getElementById('stores-data');
        const allStores = storesDataElement ? JSON.parse(storesDataElement.dataset.stores) : [];

        const resourceModalEl = document.getElementById('resourceModal');
        if (resourceModalEl) {
            const resourceModal = new bootstrap.Modal(resourceModalEl);
            const resourceForm = document.getElementById('resourceForm');
            const modalTitle = document.getElementById('resourceModalLabel');
            const modalBody = resourceModalEl.querySelector('.modal-body');
            const modalMethodField = document.getElementById('modal-method-field');

            // This single listener handles all create and edit buttons
            document.querySelectorAll('[data-action="create"], [data-action="edit"]').forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const formConfig = JSON.parse(this.dataset.formConfig);

                    // --- 1. Set Modal Title ---
                    modalTitle.textContent = (action === 'create' ? `{{__('New')}} ` : `{{__('Edit')}} `) + formConfig.resourceName;

                    // --- 2. Build the standard form fields (name, comments, etc.) ---
                    let formHtml = '';
                    formConfig.fields.forEach((field, index) => {
                        const isRequired = (field === 'comments') ? '' : 'required';
                        const value = (action === 'edit') ? JSON.parse(this.dataset.resource)[field] : '';
                        const fieldLabel = formConfig.columns[index];
                        formHtml += `<div class="mb-3"><label for="field_${field}" class="form-label">${fieldLabel}</label><input type="text" class="form-control" name="${field}" id="field_${field}" value="${value || ''}" ${isRequired}></div>`;
                    });

                    // --- 3. Build the "Assign to Stores" checkbox list ---
                    if (formConfig.resourceName !== 'Store') {
                        let assignedStoreIds = [];
                        // If we are editing, get the IDs of stores already assigned
                        if (action === 'edit') {
                            const resource = JSON.parse(this.dataset.resource);
                            if (resource.stores) {
                                assignedStoreIds = resource.stores.map(store => store.id);
                            }
                        }

                        formHtml += `<div class="mb-3">
                                    <label class="form-label">{{__('Assign to Stores')}}</label>
                                    <div class="p-2 border rounded" style="max-height: 150px; overflow-y: auto;">`;

                        allStores.forEach(store => {
                            const isChecked = assignedStoreIds.includes(store.id) ? 'checked' : '';
                            formHtml += `<div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="stores[]" value="${store.id}" id="store-${store.id}" ${isChecked}>
                                        <label class="form-check-label" for="store-${store.id}">${store.name}</label>
                                     </div>`;
                        });

                        formHtml += `</div></div>`;
                    }

                    // --- 4. Put the generated HTML into the modal body ---
                    modalBody.innerHTML = formHtml;

                    // --- 5. Configure the form's submission URL and method ---
                    if (action === 'create') {
                        resourceForm.action = `{{ url('/settings') }}/${formConfig.baseRouteName.replace('settings.','')}`;
                        resourceForm.reset();
                        modalMethodField.innerHTML = '';
                    } else { // action is 'edit'
                        const resource = JSON.parse(this.dataset.resource);
                        const updateUrl = `{{ url('/settings') }}/${formConfig.baseRouteName.replace('settings.','')}/${resource.id}`;
                        resourceForm.action = updateUrl;
                        modalMethodField.innerHTML = '@method("PUT")';
                    }

                    // --- 6. Finally, show the modal ---
                    resourceModal.show();
                });
            });
        }

        // --- SortableJS Logic ---
        document.querySelectorAll('.sortable-table').forEach(tbody => {
            const updateOrderRoute = tbody.dataset.updateOrderRoute;

            if (tbody && updateOrderRoute) { // Check if tbody and route exist
                new Sortable(tbody, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: function() {
                        const newOrder = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.dataset.id);

                        fetch(updateOrderRoute, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    ids: newOrder
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    showToast("{{__('Order saved successfully!')}}");
                                }
                            })
                            .catch(error => console.error('Error saving order:', error));
                    }
                });
            }
        });

        // --- Toast Notification Logic ---
        const successToast = document.getElementById('settings-success-toast');
        if (successToast) {
            showToast(successToast.dataset.message);
        }

        function showToast(message) {
            const toastContainer = document.querySelector('.toast-container');
            const toastHTML = `<div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`;
            toastContainer.innerHTML = toastHTML;
            new bootstrap.Toast(toastContainer.querySelector('.toast'), {
                delay: 3000
            }).show();
        }
    });
</script>
@endpush