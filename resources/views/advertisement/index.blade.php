@extends('layouts.main')

@section('title')
    {{ __('Advertisement') }}
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
        {{-- create add btn --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="javascript:" onclick="setValue(0)" class="btn btn-primary">إضافه اعلان مميز</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('advertisement_list') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-search-align="right"
                            data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                            data-fixed-columns="true" data-fixed-number="1" data-fixed-right-number="1"
                            data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-align="center" data-sortable="true">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="type" data-align="center" data-sortable="false">
                                        {{ __('Type') }}</th>
                                    <th scope="col" data-field="image" data-align="center" data-sortable="false" data-visible="false">
                                        {{ __('Image') }}
                                    </th>
                                    <th scope="col" data-field="start_date" data-align="center" data-sortable="false" data-visible="false">
                                        {{ __('Start Date') }}</th>
                                    <th scope="col" data-field="end_date" data-align="center" data-sortable="false" data-visible="false">
                                        {{ __('End Date') }}</th>
                                    <th scope="col" data-field="title" data-align="center">
                                        {{ __('Title') }}</th>
                                    <th scope="col" data-field="user_name" data-align="center" data-sortable="true">
                                        {{ __('Customer Name') }}</th>
                                    <th scope="col" data-field="user_contact" data-align="center" data-visible="false"
                                        data-sortable="false">{{ __('User Contact') }}</th>
                                    <th scope="col" data-field="user_email" data-align="center" data-visible="false"
                                        data-sortable="false">{{ __('User Email') }}</th>
                                    <th scope="col" data-field="status" data-align="center" data-sortable="false">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" data-field="is_enable" data-align="center" data-sortable="false">
                                        {{ __('Status') }}</th>
                                    <th scope="col" data-field="enble_disable" data-sortable="false" data-align="center"
                                        data-width="5%">
                                        {{ __('Enable/Disable') }}</th>
                                    @if (has_permissions('update', 'property_inquiry'))
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
        <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="myModalLabel1">بيانات الاعلان المميز</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">


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
        $('#status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });

        $('#category').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        });
        $(document).ready(function() {
            var params = new window.URLSearchParams(window.location.search);
            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
        });

        function setValue(id) {
            $('#editModal .modal-body').html('انتظر قليلا');

            $.ajax({
                url : '/advertisement/' + id + '/edit',
                type : 'GET',
                success : function(data) {
                    $('#editModal .modal-body').html(data);
                    $('#editModal').modal('show');
                }
            });
        }

        function getProperties(e) {
            var customer_id = $(e.currentTarget).val();
            $.ajax({
                url: "/customer-properties/"+ customer_id,
                type: "get",
                cache: false,
                success: function(result) {
                    $('#property_id').empty()
                    result.map((item) => {
                        $('#property_id').append(`<option value="${item.id}">${item.title}</option>`);
                    });
                },
                error: function(error) {

                }
            });
        }

        function queryParams(p) {

            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
                status: $('#status').val(),
                category: $('#category').val(),
                customer_id: $('#customerid').val(),
            };
        }

        function disable(id) {
            $.ajax({
                url: "{{ route('advertisement.updateadvertisementstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "is_enable": 0,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Advertisement is Disable ',
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: result.message,
                            duration: 6000,
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

        function active(id) {
            $.ajax({
                url: "{{ route('advertisement.updateadvertisementstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "is_enable": 1,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Advertisement is Enable',
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: result.message,
                            duration: 6000,
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
