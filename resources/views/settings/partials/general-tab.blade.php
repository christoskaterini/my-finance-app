<form action="{{ route('settings.updateGeneral') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- Column for General Settings --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{__('General Settings')}}</h5>
                </div>
                <div class="card-body">
                    {{-- Application Name --}}
                    <div class="mb-4">
                        <label for="app_name" class="form-label">{{__('Application Name')}}</label>
                        <input type="text" class="form-control" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? 'Finance Studio' }}" required>
                    </div>
                    {{-- Application Theme --}}
                    <div class="mb-4">
                        <label class="form-label">{{__('Application Theme')}}</label>
                        <div class="form-check"><input class="form-check-input" type="radio" name="app_theme" id="theme_light" value="light" @if(($settings['app_theme'] ?? 'dark' )=='light' ) checked @endif><label class="form-check-label" for="theme_light">{{__('Light Mode')}}</label></div>
                        <div class="form-check"><input class="form-check-input" type="radio" name="app_theme" id="theme_dark" value="dark" @if(($settings['app_theme'] ?? 'dark' )=='dark' ) checked @endif><label class="form-check-label" for="theme_dark">{{__('Dark Mode')}}</label></div>
                    </div>
                    {{-- Application Logo & Favicon --}}
                    <div class="mb-4">
                        <label class="form-label">{{__('Application Logo & Favicon')}}</label>
                        @if(isset($settings['app_logo']))
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light rounded d-inline-block border border-2 border-secondary">
                                <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Current Logo" style="max-height: 80px;">
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-outline-secondary ms-3" data-bs-toggle="modal" data-bs-target="#logoModal">
                                    {{ __('Change Logo') }}
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#logoModal">
                                {{ __('Upload Logo') }}
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Column for Regional Settings --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0">{{__('Regional Settings')}}</h5>
                </div>
                <div class="card-body">
                    {{-- Language Setting --}}
                    <div class="mb-4">
                        <label for="locale" class="form-label">{{__('Language')}}</label>
                        <select class="form-select" id="locale" name="locale">
                            @php
                            $currentLocale = old('locale', session('locale', $settings['app_locale'] ?? config('app.locale')));
                            @endphp
                            @foreach (config('languages') as $localeCode => $name)
                            <option value="{{ $localeCode }}" @selected($currentLocale==$localeCode)>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('locale')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Timezone Setting --}}
                    <div class="mb-4">
                        <label for="app_timezone" class="form-label">{{__('Timezone')}}</label>
                        <select class="form-select" id="app_timezone" name="app_timezone">
                            @foreach($timezones as $timezone)
                            <option value="{{ $timezone }}" @if(($settings['app_timezone'] ?? 'UTC' )==$timezone) selected @endif>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Currency Symbol --}}
                    <div class="mb-4">
                        <label for="app_currency_symbol" class="form-label">{{__('Currency Symbol')}}</label>
                        <input type="text" class="form-control" id="app_currency_symbol" name="app_currency_symbol" value="{{ $settings['app_currency_symbol'] ?? '€' }}" required>
                    </div>
                    {{-- Currency Position Setting --}}
                    <div class="mb-4">
                        <label class="form-label">{{__('Currency Position')}}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="app_currency_position" id="pos_before" value="before" @if(($settings['app_currency_position'] ?? 'after' )=='before' ) checked @endif>
                            <label class="form-check-label" for="pos_before">{{__('Before number (e.g., $100.00)')}}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="app_currency_position" id="pos_after" value="after" @if(($settings['app_currency_position'] ?? 'after' )=='after' ) checked @endif>
                            <label class="form-check-label" for="pos_after">{{__('After number (e.g., 100.00 €)')}}</label>
                        </div>
                    </div>
                    {{-- Number Format --}}
                    <div class="mb-4">
                        <label class="form-label">{{__('Number Format')}}</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="app_number_format" id="format_dot" value="." @if(($settings['app_number_format'] ?? ',' )=='.' ) checked @endif>
                            <label class="form-check-label" for="format_dot">{{__('1,234.56 (Dot for decimal)')}}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="app_number_format" id="format_comma" value="," @if(($settings['app_number_format'] ?? ',' )==',' ) checked @endif>
                            <label class="form-check-label" for="format_comma">{{__('1.234,56 (Comma for decimal)')}}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Button --}}
    <div class="text-end">
        <button type="submit" class="btn btn-primary">{{__('Save General Settings')}}</button>
    </div>
</form>