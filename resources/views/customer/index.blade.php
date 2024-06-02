@extends('layouts.main')

@section('title')
    {{ __('Customer') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>

            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">

            </div>
        </div>
    </div>
@endsection


@section('content')
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('customerList') . (request('id') ? '?id='. request('id') : '') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                            {{-- data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false" --}}
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams" data-show-export="true"
                            data-export-options='{ "fileName": "data-list-<?= date('d-m-y') ?>" }'>
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-align="center">{{ __('ID') }}</th>
                                    <th scope="col" data-field="profile" data-sortable="false" data-align="center">{{ __('Profile') }}</th>
                                    <th scope="col" data-field="name" data-sortable="true" data-align="center">{{ __('Name') }}</th>
                                    <th scope="col" data-field="mobile" data-sortable="true" data-align="center">{{ __('Number') }}</th>
                                    <th scope="col" data-field="address" data-visible="false" data-sortable="false" data-align="center">{{ __('Address') }}</th>
                                    <th scope="col" data-field="customertotalpost" data-sortable="false" data-align="center">{{ __('Total Post') }}</th>
                                    <th scope="col" data-field="role" data-sortable="true" data-align="center">{{ __('Agent') }}</th>
                                    <th scope="col" data-field="verified" data-sortable="true" data-align="center">{{ __('Verified') }}</th>
                                    <th scope="col" data-field="isActive" data-sortable="false" data-align="center">{{ __('Active Status') }}</th>
                                    <th scope="col" data-field="subscription" data-sortable="false" data-align="center">{{ __('Package') }}</th>
                                    <th scope="col" data-field="subscription_startdate" data-visible="true" data-sortable="false" data-align="center">{{ __('Start Date') }}</th>
                                    <th scope="col" data-field="subscription_enddate" data-visible="true" data-sortable="false" data-align="center">{{ __('End Date') }}</th>
                                    <th scope="col" data-field="enble_disable" data-sortable="false" data-align="center">{{ __('Enable/Disable') }}</th>
                                    @if (has_permissions('update', 'customers'))
                                        <th scope="col" data-field="operate" data-align="center" data-sortable="false">
                                            {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODEL MODEL -->
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">{{ __('Change User Package') }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ url('updatePackage') }}" class="form-horizontal" enctype="multipart/form-data" method="POST" data-parsley-validate>
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-sm-12">
                                    <select name="edit_user_package" id="edit_user_package" class="chosen-select form-select"
                                        style="width: 100%">
                                        @if (isset($package))
                                            @foreach ($package as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <input type="hidden" name="id" id="id">
                                </div>
                            </div>

                            <div class="modal-footer" style="padding: 2% 0%">
                                <button type="submit" class="btn btn-primary waves-effect waves-light" name="action" value="change">{{ __('Change Package') }}</button>
                                <button type="submit" class="btn btn-danger waves-effect waves-light" name="action" value="renew">{{ __('Renew Package') }}</button>
                                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <input type="hidden" id="customerid" value="{{ isset($_GET['customer']) ? $_GET['customer'] : '' }}">
    </section>

@endsection


@section('script')
    <script>
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search
            };
        }

        function packageModalData(id) {
        }

        function setValue(id) {
            $("#id").val(id);
        }

        function chk(checkbox) {

            if (checkbox.checked) {

                active(event.target.name,0,0);

            } else {

                disable(event.target.name,0,0);
            }
        }

        function chk1(checkbox) {

            if (checkbox.checked) {

                active(event.target.name,1,0);

            } else {

                disable(event.target.name,1,0);
            }
        }

        function chk2(checkbox) {

            if (checkbox.checked) {

                active(event.target.name,0,1);

            } else {

                disable(event.target.name,0,1);
            }
        }

        function disable(id,role,verified) {
            $.ajax({
                url: "{{ route('customer.customerstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "agent": role,
                    "verified": verified,
                    "status": 0,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Customer Changed successfully',
                            duration: 4000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: "Something Went Wrong",
                            duration: 4000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }

                },
                error: function(error) {

                }
            });
        }

        function active(id,role,verified) {
            $.ajax({
                url: "{{ route('customer.customerstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "agent": role,
                    "verified": verified,
                    "status": 1,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Customer Changed successfully',
                            duration: 4000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: "Something Went Wrong",
                            duration: 4000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    }
                },
                error: function(error) {

                }
            });
        }
    </script>
@endsection
