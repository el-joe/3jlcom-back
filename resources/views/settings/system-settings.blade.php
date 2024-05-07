@extends('layouts.main')

@section('title')
    {{ __('System Settings') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first"></div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <form class="form" action="{{ url('set_settings') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}

            <div class="card">
                <div class="card-body">
                    <div class="divider">
                        <h6 class="divider-text">{{ __('Company Details') }}</h6>
                    </div>
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">{{ __('Company Name') }}</label>
                                <div class="col-sm-4">
                                    <input name="company_name" type="text" class="form-control" id="company_name"
                                        placeholder="Company Name"
                                        value="{{ system_setting('company_name') != '' ? system_setting('company_name') : '' }}"
                                        required="">
                                </div>
                                <label class="col-sm-2 col-form-label ">{{ __('Email') }}</label>
                                <div class="col-sm-4">
                                    <input name="company_email" type="email" class="form-control" placeholder="Email"
                                        value="{{ system_setting('company_email') != '' ? system_setting('company_email') : '' }}"
                                        required="">
                                </div>

                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">{{ __('Contact Number 1') }}</label>
                                <div class="col-sm-4">
                                    <input name="company_tel1" type="number" class="form-control"
                                        placeholder="Contact Number 1" maxlength="10"
                                        onKeyDown="if(this.value.length==16 && event.keyCode!=8) return false;"
                                        value="{{ system_setting('company_tel1') != '' ? system_setting('company_tel1') : '' }}">

                                </div>
                                <label class="col-sm-2 col-form-label ">{{ __('Contact Number 2') }}</label>
                                <div class="col-sm-4">
                                    <input name="company_tel2" type="number" class="form-control"
                                        placeholder="Contact Number 2" maxlength="10"
                                        onKeyDown="if(this.value.length==16 && event.keyCode!=8) return false;"
                                        value="{{ system_setting('company_tel2') != '' ? system_setting('company_tel2') : '' }}"
                                        required="">

                                </div>

                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">
                    <div class="divider">
                        <h6 class="divider-text">{{ __('Images') }}</h6>
                    </div>
                    <div class="row mt-1">
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-1 col-form-label ">{{ __('Favicon Icon') }}</label>
                                <div class="col-sm-3">
                                    <input class="filepond" type="file" name="favicon_icon" id="favicon_icon">
                                    <img src="{{ url('assets/images/logo/favicon.png') }}" class="mt-2 favicon_icon"
                                        alt="image" style="width: 25%;">
                                </div>
                                <label class="col-sm-1 col-form-label ">{{ __('Comapny Logo') }}</label>
                                <div class="col-sm-3">
                                    <input class="filepond" type="file" name="company_logo" id="company_logo">
                                    <img src="{{ url('assets/images/logo/logo.png') }}" class="mt-2 company_logo"
                                        alt="image" style="width: 25%;">
                                </div>
                                <label class="col-sm-1 col-form-label ">{{ __('Login Page Image') }}</label>
                                <div class="col-sm-3">
                                    <input class="filepond" type="file" name="login_image" id="login_image">
                                    <img src="{{ url('assets/images/bg/Login_BG.jpg') }}" class="mt-2 login_image"
                                        alt="image" style="width: 25%;">

                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">
                    <div class="divider">
                        <h6 class="divider-text">{{ __('Paypal Setting') }}</h6>
                    </div>

                    <div class="form-group row mt-2">
                        <label class="col-sm-2 form-check-label">{{ __('Paypal Business ID') }}</label>
                        <div class="col-sm-4">
                            <input name="paypal_business_id" type="text" class="form-control"
                                placeholder="Paypal Business ID"
                                value="{{ system_setting('paypal_business_id') != '' ? system_setting('paypal_business_id') : '' }}"
                                required="">
                        </div>

                        <label class="col-sm-2 form-check-label">{{ __('Paypal Webhook URL') }}</label>
                        <div class="col-sm-4">
                            <input name="paypal_webhook_url" type="text" class="form-control"
                                placeholder="Paypal Webhook URL"
                                value="{{ system_setting('paypal_webhook_url') != '' ? system_setting('paypal_webhook_url') : url('/webhook/paypal') }}"
                                required="">
                        </div>

                        <label class="col-sm-2 form-check-label mt-3">{{ __('Sandbox Mode') }}</label>
                        <div class="col-sm-2 col-md-4 col-xs-12 mt-3">
                            <div class="form-check form-switch">

                                <input type="hidden" name="sandbox_mode" id="sandbox_mode"
                                    value="{{ system_setting('sandbox_mode') != '' ? system_setting('sandbox_mode') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch" {{
                                    system_setting('sandbox_mode')=='1' ? 'checked' : '' }} id="switch_sandbox_mode">
                                <label class="form-check-label" for="switch_sandbox_mode"></label>
                            </div>
                        </div>

                        <label class="col-sm-2 form-check-label mt-3">{{ __('Enable/Disable') }}</label>
                        <div class="col-sm-2 col-md-4 col-xs-12 mt-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="paypal_gateway" id="paypal_gateway"
                                    value="{{ system_setting('paypal_gateway') != '' ? system_setting('paypal_gateway') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op'
                                    {{ system_setting('paypal_gateway')=='1' ? 'checked' : '' }} id="switch_paypal_gateway">
                                <label class="form-check-label" for="switch_paypal_gateway" id='lbl_paypal'>
                                    {{ system_setting('paypal_gateway') != '' ? (system_setting('paypal_gateway') == 0 ? 'Disable' : 'Enable') : __('Disable') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="divider pt-3">
                        <h6 class="divider-text">{{ __('Stripe Setting') }}</h6>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 form-check-label  mt-3">{{ __('Stripe publishable key') }}</label>
                        <div class="col-sm-4 mt-3">
                            <input name="stripe_publishable_key" type="text" class="form-control"
                                placeholder="Stripe publishable  Key"
                                value="{{ system_setting('stripe_publishable_key') != '' ? system_setting('stripe_publishable_key') : '' }}"
                                required="">
                        </div>
                        <label class="col-sm-2 form-check-label  mt-3">{{ __('Stripe Webhook URL') }}</label>
                        <div class="col-sm-4 mt-3">
                            <input name="stripe_webhook_url" type="text" class="form-control"
                                placeholder="Stripe Webhook URL"
                                value="{{ system_setting('stripe_webhook_url') != '' ? system_setting('stripe_webhook_url') : url('/webhook/stripe') }}"
                                required="">
                        </div>
                        <label class="col-sm-2 form-check-label  mt-3">{{ __('Stripe Currency Symbol') }}</label>

                        <div class="col-sm-4 mt-3">

                            <select name="stripe_currency" id="stripe_currency" class="select2 form-select form-control-sm">
                                @foreach ($stripe_currencies as $value)
                                <option value={{ $value }}
                                    {{ system_setting('stripe_currency') == $value ? 'selected' : '' }}>
                                    {{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="col-sm-2 form-check-label  mt-3" id='lbl_stripe'>{{ system_setting('stripe_gateway')
                            != '' ? (system_setting('stripe_gateway') == 0 ? 'Disable' : 'Enable') : 'Disable' }}</label>
                        <div class="col-sm-2 col-md-4 col-xs-12  mt-3">

                            <div class="form-check form-switch ">

                                <input type="hidden" name="stripe_gateway" id="stripe_gateway"
                                    value="{{ system_setting('stripe_gateway') != '' ? system_setting('stripe_gateway') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch" class="switch-input" name='op'
                                    {{ system_setting('stripe_gateway')=='1' ? 'checked' : '' }} id="switch_stripe_gateway">

                            </div>
                        </div>
                        <label class="col-sm-2 form-check-label  mt-3">{{ __('Stripe Secret key') }}</label>
                        <div class="col-sm-4 mt-3">
                            <input name="stripe_secret_key" type="text" class="form-control" placeholder="Stripe Secret Key"
                                value="{{ system_setting('stripe_secret_key') != '' ? system_setting('stripe_secret_key') : '' }}"
                                required="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">

                    <div class="divider">
                        <h6 class="divider-text">{{ __('More Setting') }}</h6>

                    </div>

                    <div class="form-group row mt-3">

                        <label class="col-sm-2 form-check-label ">{{ __('Default Language') }}</label>
                        <div class="col-sm-2 col-md-4 col-xs-12 ">
                            <select name="default_language" id="default_language"
                                class="select2 form-select form-control-sm">

                                @foreach ($languages as $row)
                                    {{ $row }}
                                    <option value="{{ $row->code }}"
                                        {{ system_setting('default_language') == $row->code ? 'selected' : '' }}>
                                        {{ $row->name }}
                                    </option>
                                @endforeach
                            </select>


                        </div>
                        <label class="col-sm-2 form-check-label ">{{ __('Currency Symbol') }}</label>
                        <div class="col-sm-4">
                            <input name="currency_symbol" type="text" class="form-control"
                                placeholder="Currency Symbol"
                                value="{{ system_setting('currency_symbol') != '' ? system_setting('currency_symbol') : '' }}"
                                required="">
                        </div>

                    </div>

                    <div class="form-group row mt-3">

                        <label class="col-sm-2 form-check-label ">{{ __('IOS Version') }}</label>

                        <div class="col-sm-4">
                            <input name="ios_version" type="text" class="form-control" placeholder="App Version"
                                value="{{ system_setting('ios_version') != '' ? system_setting('ios_version') : '' }}"
                                required="">
                        </div>
                        <label class="col-sm-2 form-check-label ">{{ __('Android Version') }}</label>

                        <div class="col-sm-4">
                            <input name="android_version" type="text" class="form-control" placeholder="App Version"
                                value="{{ system_setting('android_version') != '' ? system_setting('android_version') : '' }}"
                                required="">
                        </div>
                    </div>
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 form-check-label ">{{ __('Maintenance Mode') }}</label>
                        <div class="col-sm-4">
                            <div class="form-check form-switch  ">

                                <input type="hidden" name="maintenance_mode" id="maintenance_mode"
                                    value="{{ system_setting('maintenance_mode') != '' ? system_setting('maintenance_mode') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ system_setting('maintenance_mode') == '1' ? 'checked' : '' }}
                                    id="switch_maintenance_mode">
                                <label class="form-check-label" for="switch_maintenance_mode"></label>
                            </div>

                        </div>
                        <label class="col-sm-2 form-check-label ">{{ __('Place API Key') }}</label>

                        <div class="col-sm-4">
                            <input name="place_api_key" type="text" class="form-control" placeholder="Place API Key"
                                value="{{ system_setting('place_api_key') != '' ? system_setting('place_api_key') : '' }}"
                                required="">
                        </div>

                    </div>
                    <div class="form-group row mt-3">
                        <label class="col-sm-2 form-check-label">{{ __('Force Update') }}</label>
                        <div class="col-sm-4">
                            <div class="form-check form-switch">

                                <input type="hidden" name="force_update" id="force_update"
                                    value="{{ system_setting('force_update') != '' ? system_setting('force_update') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ system_setting('force_update') == '1' ? 'checked' : '' }} id="switch_force_update">
                                <label class="form-check-label" for="switch_force_update"></label>
                            </div>
                        </div>
                        <label class="col-sm-2 form-check-label ">{{ __('Number With Suffix') }}</label>
                        <div class="col-sm-2 col-md-4 col-xs-12 ">
                            <div class="form-check form-switch  ">

                                <input type="hidden" name="number_with_suffix" id="number_with_suffix"
                                    value="{{ system_setting('number_with_suffix') != '' ? system_setting('number_with_suffix') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ system_setting('number_with_suffix') == '1' ? 'checked' : '' }}
                                    id="switch_number_with_suffix">

                            </div>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <label class="col-sm-2 form-check-label">{{ __('Require Ads Approval') }}</label>
                        <div class="col-sm-4">
                            <div class="form-check form-switch">

                                <input type="hidden" name="require_approval" id="require_approval"
                                    value="{{ system_setting('require_approval') != '' ? system_setting('require_approval') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ system_setting('require_approval') == '1' ? 'checked' : '0' }} id="switch_require_approval">
                                <label class="form-check-label" for="switch_require_approval"></label>
                            </div>
                        </div>
                        <label class="col-sm-2 form-check-label">{{ __('Require Caysh Approval') }}</label>
                        <div class="col-sm-4">
                            <div class="form-check form-switch">

                                <input type="hidden" name="require_caysh_approval" id="require_caysh_approval"
                                    value="{{ system_setting('require_caysh_approval') != '' ? system_setting('require_caysh_approval') : 0 }}">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    {{ system_setting('require_caysh_approval') == '1' ? 'checked' : '0' }} id="switch_require_caysh_approval">
                                <label class="form-check-label" for="switch_require_caysh_approval"></label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card mt-2">
                <div class="card-body">

                    <div class="divider">
                        <h6 class="divider-text">{{ __('Notification FCM Key') }}</h6>
                    </div>

                    <div class="form-group row mt-3">
                        <label class="col-sm-2 form-check-label ">{{ __('FCM Key') }}</label>
                        <div class="col-sm-10 col-md-10 col-xs-12 ">
                            <textarea name="fcm_key" class="form-control" rows="3" placeholder="Fcm Key">{{ system_setting('fcm_key') != '' ? system_setting('fcm_key') : '' }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer mt-2">
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" name="btnAdd" value="btnAdd"
                        class="btn btn-primary me-1 mb-1">{{ __('Save') }}</button>
                </div>
            </div>

        </form>

    </section>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on('click', '#favicon_icon', function(e) {
            $('.favicon_icon').hide();
        });

        $(document).on('click', '#company_logo', function(e) {
            $('.company_logo').hide();
        });

        $(document).on('click', '#login_image', function(e) {
            $('.login_image').hide();
        });

        $(document).ready(function() {
            var companyname = $('#company_name').val();
            sessionStorage.setItem('comapanyname', $('#company_name').val());
            const newValue = `"${companyname}"`;
        });

        const checkboxes = document.querySelectorAll('input[type=checkbox][role=switch][name=op]', );
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', (event) => {
                if (event.target.checked) {
                    checkboxes.forEach((checkbox) => {
                        if (checkbox !== event.target) {
                            checkbox.checked = false;

                            $("#switch_paypal_gateway").is(':checked') ? $("#paypal_gateway").val(
                                    1) : $("#paypal_gateway")
                                .val(0);

                            $("#switch_paypal_gateway").is(':checked') ? $("#lbl_paypal").text(
                                    "Enable") : $("#lbl_paypal")
                                .text("Disable");

                            $("#switch_razorpay_gateway").is(':checked') ? $("#razorpay_gateway")
                                .val(1) : $("#razorpay_gateway")
                                .val(0);
                            $("#switch_razorpay_gateway").is(':checked') ? $("#lbl_razorpay").text(
                                    "Enable") : $("#lbl_razorpay")
                                .text("Disable");

                            $("#switch_paystack_gateway").is(':checked') ? $("#lbl_paystack").text(
                                    "Enable") : $("#lbl_paystack")
                                .text("Disable");

                            $("#switch_paystack_gateway").is(':checked') ? $("#paystack_gateway")
                                .val(1) : $("#paystack_gateway")
                                .val(0);

                            $("#switch_stripe_gateway").is(':checked') ? $("#lbl_stripe").text(
                                    "Enable") : $("#lbl_stripe")
                                .text("Disable");

                            $("#switch_stripe_gateway").is(':checked') ? $("#stripe_gateway")
                                .val(1) : $("#stripe_gateway")
                                .val(0);
                        }
                    });
                }
            });
        });

        $("#switch_maintenance_mode").on('change', function() {
            $("#switch_maintenance_mode").is(':checked') ? $("#maintenance_mode").val(1) : $("#maintenance_mode")
                .val(0);
        });

        $("#switch_force_update").on('change', function() {
            $("#switch_force_update").is(':checked') ? $("#force_update").val(1) : $("#force_update")
                .val(0);
        });

        $("#switch_require_approval").on('change', function() {
            $("#switch_require_approval").is(':checked') ? $("#require_approval").val(1) : $("#require_approval")
                .val(0);
        });

        $("#switch_require_caysh_approval").on('change', function() {
            $("#switch_require_caysh_approval").is(':checked') ? $("#require_caysh_approval").val(1) : $("#require_caysh_approval")
                .val(0);
        });

        $("#switch_number_with_suffix").on('change', function() {
            $("#switch_number_with_suffix").is(':checked') ? $("#number_with_suffix").val(1) : $(
                    "#number_with_suffix")
                .val(0);
        });

        $("#switch_sandbox_mode").on('change', function() {
            $("#switch_sandbox_mode").is(':checked') ? $("#sandbox_mode").val(1) : $("#sandbox_mode")
                .val(0);
        });

        $("#switch_paypal_gateway").on('change', function() {

            $("#switch_paypal_gateway").is(':checked') ? $("#paypal_gateway").val(1) : $("#paypal_gateway")
                .val(0);

            $("#switch_paypal_gateway").is(':checked') ? $("#lbl_paypal").text("Disable") : $("#lbl_paypal")
                .text("Enable");
        });

        $("#switch_razorpay_gateway").on('change', function() {

            $("#switch_razorpay_gateway").is(':checked') ? $("#razorpay_gateway").val(1) : $("#razorpay_gateway")
                .val(0);

            $("#switch_razorpay_gateway").is(':checked') ? $("#lbl_razorpay").text("Disable") : $("#lbl_razorpay")
                .text("Enable");
        });

        $("#switch_stripe_gateway").on('change', function() {

            $("#switch_stripe_gateway").is(':checked') ? $("#stripe_gateway").val(1) : $("#stripe_gateway")
                .val(0);

            $("#switch_stripe_gateway").is(':checked') ? $("#lbl_stripe").text("Disable") : $("#lbl_stripe")
                .text("Enable");
        });

        $("#switch_paystack_gateway").on('change', function() {

            $("#switch_paystack_gateway").is(':checked') ? $("#paystack_gateway").val(1) : $("#paystack_gateway")
                .val(0);

            $("#switch_paystack_gateway").is(':checked') ? $("#lbl_paystack").text("Disable") : $("#lbl_paystack")
                .text("Enable");
        });
    </script>
@endsection

