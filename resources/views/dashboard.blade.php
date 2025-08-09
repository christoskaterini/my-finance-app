@extends('layouts.studio')
@section('page-title', __('Dashboard'))

@section('content')
<div class="row">
    <div class="col-xl-9 col-lg-10">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('Select a Store to Begin') }}</h5>
            </div>
            <div class="card-body">
                @if($stores->isEmpty())
                <div class="alert alert-warning mb-0"><a href="{{ url('/settings?tab=stores') }}" class="alert-link">{{ __('Please add a store to continue.') }}</a></div>
                @else
                <div id="store-selector" class="d-flex flex-wrap gap-3">
                    @foreach($stores as $store)
                    <button class="btn btn-outline-secondary btn-lg store-btn" data-store-id="{{ $store->id }}" data-store-name="{{ $store->name }}">
                        <i class="bi bi-shop-window me-2"></i> {{ $store->name }}
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <div id="main-action-area" class="text-center mt-4" style="display: none;">
            {{-- This is now a link, not a button --}}
            <a href="#" id="add-record-link" class="btn btn-primary btn-lg" style="min-width: 250px;">
                <i class="bi bi-plus-circle-dotted me-2"></i> {{ __('Add Record') }}
            </a>
            <div class="mt-2 text-muted" id="active-store-label"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Store Selector Logic ---
        const storeSelector = document.getElementById('store-selector');
        if (storeSelector) {
            const storeButtons = storeSelector.querySelectorAll('.store-btn');
            const mainActionArea = document.getElementById('main-action-area');
            const activeStoreLabel = document.getElementById('active-store-label');
            const addRecordLink = document.getElementById('add-record-link');

            const handleStoreButtonClick = (button) => {
                storeButtons.forEach(btn => btn.classList.replace('btn-primary', 'btn-outline-secondary'));
                button.classList.replace('btn-outline-secondary', 'btn-primary');

                const selectedStoreId = button.dataset.storeId;
                const selectedStoreName = button.dataset.storeName;

                addRecordLink.href = `{{ route('transactions.create') }}?store_id=${selectedStoreId}`;

                mainActionArea.style.display = 'block';
                activeStoreLabel.textContent = `{{ __('for') }} "${selectedStoreName}"`;
            };

            storeButtons.forEach(button => button.addEventListener('click', () => handleStoreButtonClick(button)));

            if (storeButtons.length > 0) {
                handleStoreButtonClick(storeButtons[0]);
            }
        }

        // --- Toast Logic ---
        const toastEl = document.querySelector('.toast-container .toast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 4000
            });
            toast.show();
        }
    });
</script>
@endpush