@extends('layouts.main')

@section('title')
{{ __('Manufacturers') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 mb-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
            </div>

        </div>
    </div>
@endsection


@section('content')

    <section class="section">
            <div class="accordion mb-3" id="accordionExample">
                <div class="accordion-item">
                    <h4 class="accordion-header" id="headingOne">
                        <button class="accordion-button primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                          {{ __('Create New Manufacturer') }}
                        </button>
                    </h4>
                
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <div class="row">
                                
                                {!! Form::open(['url' => route('manufacturers.store'), 'data-parsley-validate', 'files' => true]) !!}
                                <div class=" row">
                                    
                                    <div class="col-md-6 col-sm-12 form-group mandatory">
                                        {{ Form::label('manufacturer', __('Manufacturer'), ['class' => 'form-label text-center text-primary']) }}
                                        {{ Form::text('manufacturer', '', ['class' => 'form-control', 'placeholder' => __('Manufacturer'), 'data-parsley-required' => 'true']) }}
                                    </div>
        
                                    <div class="col-md-6 col-sm-12 form-group mandatory">
                                        {{ Form::label('manufacturer_ar', __('Manufacturer Ar'), ['class' => 'form-label text-center text-primary']) }}
                                        {{ Form::text('manufacturer_ar', '', ['class' => 'form-control', 'placeholder' => __('Manufacturer Ar'), 'data-parsley-required' => 'true']) }}
                                    </div>
        
                                    <div class="col-md-6 col-sm-12 form-group mandatory">
                                        {{ Form::label('image', __('Image'), ['class' => 'form-label text-center text-primary']) }}
        
                                        {{ Form::file('image', ['class' => 'form-control', 'data-parsley-required' => 'true', 'accept' => 'image/*']) }}
        
                                    </div>
                                    <div class="col-sm-4 justify-content-end" style="margin-top:2%; display:flex;">
                                        {{ Form::submit(__('Save'), ['class' => 'btn btn-block btn-primary me-1 mb-1']) }}
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('manufacturersList') }}" data-click-to-select="true"
                            data-responsive="true" data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                            data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false"
                            data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3"
                            data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-sortable="true" data-align="center">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="image" data-sortable="false" data-align="center">
                                        {{ __('Image') }}
                                    </th>
                                    <th scope="col" data-field="manufacturer" data-sortable="true" data-align="center">
                                        {{ __('Manufacturer') }}</th>
                                    <th scope="col" data-field="manufacturer_ar" data-sortable="true" data-align="center">
                                        {{ __('Manufacturer Ar') }}</th>
                                    <th scope="col" data-field="status" data-sortable="true" data-align="center">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" data-field="enable_disable" data-sortable="false"
                                        data-align="center">
                                        {{ __('Enable/Disable') }}
                                    </th>

                                    <th scope="col" data-field="operate" data-sortable="false" data-align="center">
                                        {{ __('Action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- EDIT MODEL MODEL -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="myModalLabel1">{{ __('Edit Manufacturers') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form action="{{ url('manufacturers-update') }}" class="form-horizontal" enctype="multipart/form-data"
                        method="POST" data-parsley-validate>

                        {{ csrf_field() }}

                        <input type="hidden" id="old_image" name="old_image">
                        <input type="hidden" id="edit_id" name="edit_id">
                        <div class="row">

                        </div>
                        <div class="row">

                            <div class="col-md-12 col-12 form-group">

                                {{ Form::label('image', __('Image'), ['class' => 'col-sm-12 col-form-label']) }}
                                <input type="button" class="input-btn1 input-btn1-ghost-dashed bottomleft"
                                    value="+">
                                <input accept="image/*" name='edit_image' type='file' id="edit_image"
                                    style="display: none" />
                                <img id="blah" height="100" width="110" style="margin-left: 5%;" />
                                @if (count($errors) > 0)
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger error-msg">{{ $error }}</div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mandatory">
                                    <label for="edit_manufacturer" class="form-label col-12">{{ __('Manufacturer') }}</label>
                                    <input type="text" id="edit_manufacturer" class="form-control col-12"
                                        placeholder="Name" name="edit_manufacturer" data-parsley-required="true">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group mandatory">
                                    <label for="edit_manufacturer_ar" class="form-label col-12">{{ __('Manufacturer Ar') }}</label>
                                    <input type="text" id="edit_manufacturer_ar" class="form-control col-12"
                                        placeholder="Name_ar" name="edit_manufacturer_ar" data-parsley-required="true">
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary waves-effect"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>

                    <button type="submit" class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- EDIT MODEL -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.6.6/dragula.min.js"
        integrity="sha512-MrA7WH8h42LMq8GWxQGmWjrtalBjrfIzCQ+i2EZA26cZ7OBiBd/Uct5S3NP9IBqKx5b+MMNH1PhzTsk6J9nPQQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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

        function disable(id) {
            $.ajax({
                url: "{{ route('customer.manufacturersstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 0,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Manufacturer Deactive successfully',
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: "Something Went Wrong",
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
                url: "{{ route('customer.manufacturersstatus') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    "id": id,
                    "status": 1,
                },
                cache: false,
                success: function(result) {

                    if (result.error == false) {
                        Toastify({
                            text: 'Manufacturer Active successfully',
                            duration: 6000,
                            close: !0,
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                        }).showToast();
                        $('#table_list').bootstrapTable('refresh');
                    } else {
                        Toastify({
                            text: "Something Went Wrong",
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

        function setValue(id) {
            
            $("#edit_id").val(id);
            $("#edit_manufacturer").val($("#" + id).parents('tr:first').find('td:nth-child(3)').text());
            $("#edit_manufacturer_ar").val($("#" + id).parents('tr:first').find('td:nth-child(4)').text());

            $("#old_image").val($("#" + id).data('oldimage'));
            $("#status").val($("#" + id).data('status')).trigger('change');
            src = ($("#" + id).parents('tr:first').find('td:nth-child(2)').find($('.image-popup-no-margins'))).attr('href');
            $('#blah').attr('src', src);
            $('#edit_image').attr('src', src);

        }

        $('.bottomleft').click(function() {
            $('#edit_image').click();


        });
        
        edit_image.onchange = evt => {
            console.log("click");
            const [file] = edit_image.files
            if (file) {
                blah.src = URL.createObjectURL(file)

            }


        }
    </script>
@endsection
