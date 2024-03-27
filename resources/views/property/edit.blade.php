@extends('layouts.main')

@section('title')
    {{ __('Update Product') }}
@endsection
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>

            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('property.index') }}" id="subURL">{{ __('View Property') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Upadate') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection
@section('content')
    {!! Form::open([
        'route' => ['property.update', $id],
        'method' => 'PATCH',
        'data-parsley-validate',
        'files' => true,
        'id' => 'myForm',
    ]) !!}

    <div class='row'>
        <div class='col-md-6'>
            <div class="card">
                <h3 class="card-header">{{ __('View Property') }}</h3>
                <hr>
                <div class="row card-body">
                    
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('title', __('Title'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::text('title', isset($list->title) ? $list->title : '', ['class' => 'form-control ', 'placeholder' => __('Title'), 'required' => 'true', 'id' => 'title']) }}
                    </div>
                    
                    <div class="col-md-6 form-group">
                        {{ Form::label('price', __('price') . ' ( ' . $currency_symbol . ' )', ['class' => 'form-label col-12 ']) }}
                        {{ Form::number('price', isset($list->price) ? $list->price : '', ['class' => 'form-control ', 'placeholder' => __('Price'), 'required' => 'true', 'min' => '1', 'id' => 'price']) }}
                    </div>
                    
                    <div class="col-md-6 form-group">
                        {{ Form::label('customer', __('Customer Name'), ['class' => 'form-label col-12 ']) }}
                        <select name="customer" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="customer" required='true'>
                            <option value="0">{{ __('Admin') }} </option>
                            @foreach ($customer as $row)
                                <option value="{{ $row->id }}" {{ $list->added_by == $row->id ? ' selected=selected' : '0' }}>
                                        {{ $row->name }}
                                </option>
                            @endforeach
                            <option value=""></option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 col-12 form-group mandatory">
                        {{ Form::label('description', __('Description'), ['class' => 'form-label col-12 ']) }}
                        {{ Form::textarea('description', isset($list->description) ? $list->description : '', ['class' => 'form-control mb-3', 'rows' => '4', 'id' => '', 'required' => 'true']) }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class='col-md-6'>
            <div class="card">
                <h3 class="card-header">{{ __('Details') }}</h3>
                <hr>
                <div class="card-body">
                    
                    <div class="col-md-12 col-12 form-group">
                        {{ Form::label('category', __('Category'), ['class' => 'form-label col-12 ']) }}
                        <select name="category" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="category" required='true'>
                            @foreach ($category as $row)
                                <option value="{{ $row->id }}"
                                    data-parametertypes="{{ $row->parameter_types }}" {{ $list->category_id == $row->id ? 'selected' : '' }}>
                                    {{ $row->category_ar }}
                                </option>
                            @endforeach
                            <option value=""></option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 col-12 form-group">
                        {{ Form::label('manufacturer', __('Manufacturer'), ['class' => 'form-label col-12 ']) }}
                        <select name="manufacturer" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="manufacturer" required='true'>
                            @foreach ($manufacturer as $row)
                                <option value="{{ $row->id }}" {{ $list->manufacturer_id == $row->id ? ' selected=selected' : '' }}>
                                    {{ $row->manufacturer_ar }}
                                </option>
                            @endforeach
                            <option value=""></option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 col-12 form-group">
                        {{ Form::label('model', __('Model'), ['class' => 'form-label col-12 ']) }}
                        <select name="model" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="model" required='true'>
                            @foreach ($model as $row)
                                <option value="{{ $row->id }}" {{ $list->model_id == $row->id ? ' selected=selected' : '' }}>
                                        {{ $row->model }}
                                </option>
                            @endforeach
                            <option value=""></option>
                        </select>
                    </div>
                    
                    <div class="col-md-12 col-12 form-group">
                        {{ Form::label('year', __('Year'), ['class' => 'form-label col-12 ']) }}
                        <select name="year" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="year" required='true'>
                            @foreach ($year as $row)
                                <option value="{{ $row->id }}" {{ $list->year_id == $row->id ? ' selected=selected' : '' }}>
                                        {{ $row->year }}
                                </option>
                            @endforeach
                            <option value=""></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12" id="facility">

            <div class="card">

                <h3 class="card-header">{{ __('Facilities') }}</h3>
                <hr>
                {{ Form::hidden('category_count[]', $category, ['id' => 'category_count']) }}
                {{ Form::hidden('parameter_count[]', $parameters, ['id' => 'parameter_count']) }}
                {{ Form::hidden('parameter_add', '', ['id' => 'parameter_add']) }}
                <div id="parameter_type" name=parameter_type class="row card-body">

                    @foreach ($parameters as $res)
                        @foreach ($par_arr as $key => $arr)
                            @if ($key == $res->name)
                                <div class="col-md-12 form-group mandatory">

                                    {{ Form::label($res->name, $res->name, ['class' => 'form-label col-12 font-weight-bold text-primary']) }}

                                    @if ($res->type_of_parameter == 'dropdown')
                                        <select name="{{ $res->id }}" class="select2 form-select form-control-sm"
                                            data-parsley-minSelect='1' id="" required='true'
                                            name={{ $res->id }}>

                                            <option value=""> Select Option </option>
                                            @foreach ($res->type_values as $key => $value)
                                                <option value="{{ $value }}"
                                                    {{ $arr == $value ? ' selected=selected' : '' }}>
                                                    {{ $value }} </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @if ($res->type_of_parameter == 'radiobutton')
                                        @foreach ($res->type_values as $key => $value)
                                            <input type="radio" name="{{ $res->id }}" id=""
                                                value={{ $value }} class="form-check-input"
                                                {{ $arr == $value ? 'checked' : '' }}>
                                            {{ $value }}
                                        @endforeach
                                        </select>
                                    @endif
                                    @if ($res->type_of_parameter == 'number')
                                        <input type="number" name="{{ $res->id }}" id=""
                                            class="form-control" value="{{ $arr }}">

                                        </select>
                                    @endif
                                    @if ($res->type_of_parameter == 'textbox')
                                        <input type="text" name="{{ $res->id }}" id=""
                                            class="form-control" value="{{ $arr }}">

                                        </select>
                                    @endif
                                    @if ($res->type_of_parameter == 'textarea')
                                        <textarea name="{{ $res->id }}" id="" cols="10" rows="10" value="{{ $arr }}"></textarea>
                                    @endif
                                    @if ($res->type_of_parameter == 'checkbox')
                                        @foreach ($res->type_values as $key => $value)
                                            <input type="checkbox" name="{{ $res->id . '[]' }}" id=""
                                                class="form-check-input" value="{{ $value }}"
                                                {{ !empty($arr[$key]) ? 'checked' : '' }}>

                                            {{ $value }}
                                        @endforeach
                                        </select>
                                    @endif

                                    @if ($res->type_of_parameter == 'file')
                                        <a href="{{ url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' . $arr }}"
                                            class="text-center col-12" style="text-align: center"> Click
                                            here to View</a> OR
                                        <input type="hidden" name="{{ $res->id }}" value="{{ $arr }}">
                                        <input type="file" class='form-control' name="{{ $res->id }}"
                                            id='edit_param_img'>
                                    @endif


                                </div>
                            @endif
                        @endforeach
                    @endforeach


                </div>
            </div>
        </div>
        <div class='col-md-12'>
            <div class="card">
                <h3 class="card-header">{{ __('Location') }}</h3>
                <hr>
                <div class="card-body">

                    <div class="row">
                        <div class='col-md-6'>



                            <div class="card col-md-12">
                                <input id="searchInput" class="controls" type="text" placeholder="Enter a location"
                                    style="position: absolute;left: 188px;width: 64%;height: 8%;margin-top:9px">
                            </div>
                            <div class="card col-md-12" id="map" style="height: 90%">

                                <!-- Google map -->

                            </div>
                        </div>
                        <div class='col-md-6'>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label col-12 ']) }}
                                    <select name="city" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="city" required='true'>
                                        @foreach ($city as $row)
                                            <option value="{{ $row->id }}" {{ $list->city_id == $row->id ? ' selected=selected' : '' }}>
                                                    {{ $row->city_ar }}
                                            </option>
                                        @endforeach
                                        <option value=""></option>
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    {{ Form::label('area', __('Area'), ['class' => 'form-label col-12 ']) }}
                                    <select name="area" class="select2 form-select form-control-sm" data-parsley-minSelect='1' id="area" required='true'>
                                        @foreach ($area as $row)
                                            <option value="{{ $row->id }}" {{ $list->area_id == $row->id ? ' selected=selected' : '' }}>
                                                    {{ $row->area_ar }}
                                            </option>
                                        @endforeach
                                        <option value=""></option>
                                    </select>
                                </div>
                                
                                <div class="col-md-12 col-12 form-group">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::textarea('address', isset($list->address) ? $list->address : '', ['class' => 'form-control ', 'placeholder' => __('Address'), 'rows' => '4', 'id' => 'address', 'autocomplete' => 'off']) }}
                                </div>
                                
                                <div class="col-md-6">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('country', isset($list->country) ? $list->country : '', ['class' => 'form-control ', 'placeholder' => __('Country'), 'id' => 'country']) }}
                                </div>

                                <div class="col-md-6">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('state', isset($list->state) ? $list->state : '', ['class' => 'form-control ', 'placeholder' => __('State'), 'id' => 'state']) }}
                                </div>
                                <div class="col-md-12 col-12 form-group">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::text('city', isset($list->city_name) ? $list->city_name : '', ['class' => 'form-control ', 'placeholder' => __('City'), 'id' => 'city_name']) }}
                                </div>
                                <div class="col-md-6 form-group  mandatory">
                                    {{ Form::label('latitude', __('Latitude'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::number('latitude', isset($list->latitude) ? $list->latitude : '', ['class' => 'form-control ', 'placeholder' => __('Latitude'), 'required', 'id' => 'latitude', 'step' => 'any']) }}

                                </div>
                                <div class="col-md-6 form-group  mandatory">
                                    {{ Form::label('longitude', __('Longitude'), ['class' => 'form-label col-12 ']) }}
                                    {{ Form::number('longitude', isset($list->longitude) ? $list->longitude : '', ['class' => 'form-control', 'placeholder' => __('Longitude'), 'required' => true, 'id' => 'longitude', 'step' => 'any']) }}

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <h3 class="card-header">{{ __('Images') }}</h3>
                <hr>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 card title_card">
                            {{ Form::label('title_image', __('Title Image'), ['class' => 'form-label col-12 ']) }}

                            <input type="file" class="filepond" id="filepond_title" name="title_image">
                            @if ($list->title_image)
                                <div class="card1 title_img">
                                    <img src="{{ $list->title_image }}" alt="Image" class="card1-img">

                                </div>
                            @endif
                        </div>

                        <div class="col-md-3 col-sm-12 card">
                            {{ Form::label('title_image', __('Gallary Images'), ['class' => 'form-label col-12 ']) }}
                            <input type="file" class="filepond" id="filepond2" name="gallery_images[]" multiple>
                            <?php $i = 0; ?>
                            @if (!empty($list->gallery))
                                @foreach ($list->gallery as $row)
                                    <div class="col-md-6 col-sm-12" id='{{ $row->id }}'>
                                        <div class="card1" style="height:90%;">

                                            <img src="{{ url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $list->id . '/' . $row->image }}"
                                                alt="Image" class="card1-img">
                                            <button data-rowid="{{ $row->id }}"
                                                class="RemoveBtn1 RemoveBtngallary">x</button>


                                        </div>
                                    </div>
                                    <?php $i++; ?>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-md-3">
                            {{ Form::label('video_link', __('Video Link'), ['class' => 'form-label col-12 ']) }}
                            {{ Form::text('video_link', isset($list->video_link) ? $list->video_link : '', ['class' => 'form-control ', 'placeholder' => __('Video Link'), 'id' => 'address', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class='col-md-12 d-flex justify-content-end'>
            <input type="submit" class="btn btn-primary" value="{{ __('Save') }}">
            &nbsp;
            &nbsp;

            <button class="btn btn-secondary" type="button" onclick="formname.reset();">{{ __('Reset') }}</button>
        </div>
        {!! Form::close() !!}

    </div>
@endsection
@section('script')
     <script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?libraries=places&key={{ env('MAP_KEY') }}&callback=initMap" async
        defer></script>

    <script>
        function initMap() {
            var latitude = parseFloat($('#latitude').val());
            var longitude = parseFloat($('#longitude').val());
            var map = new google.maps.Map(document.getElementById('map'), {

                center: {
                    lat: latitude,
                    lng: longitude
                },
                zoom: 13
            });
            var marker = new google.maps.Marker({
                position: {
                    lat: latitude,
                    lng: longitude
                },
                map: map,
                draggable: true,
                title: 'Marker Title'
            });
            google.maps.event.addListener(marker, 'dragend', function(event) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    'latLng': event.latLng
                }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            var address_components = results[0].address_components;
                            var city, state, country, full_address;

                            for (var i = 0; i < address_components.length; i++) {
                                var types = address_components[i].types;
                                if (types.indexOf('locality') != -1) {
                                    city = address_components[i].long_name;
                                } else if (types.indexOf('administrative_area_level_1') != -1) {
                                    state = address_components[i].long_name;
                                } else if (types.indexOf('country') != -1) {
                                    country = address_components[i].long_name;
                                }
                            }

                            full_address = results[0].formatted_address;

                            // Do something with the city, state, country, and full address

                            $('#city').val(city);
                            $('#country').val(state);
                            $('#state').val(country);
                            $('#address').val(full_address);


                            $('#latitude').val(event.latLng.lat());
                            $('#longitude').val(event.latLng.lng());

                        } else {
                            console.log('No results found');
                        }
                    } else {
                        console.log('Geocoder failed due to: ' + status);
                    }
                });
            });

            var input = document.getElementById('searchInput');
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            var infowindow = new google.maps.InfoWindow();
            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29)
            });

            autocomplete.addListener('place_changed', function() {
                infowindow.close();
                marker.setVisible(false);
                var place = autocomplete.getPlace();
                if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                marker.setIcon(({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
                }));
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                var address = '';
                if (place.address_components) {
                    address = [
                        (place.address_components[0] && place.address_components[0].short_name || ''),
                        (place.address_components[1] && place.address_components[1].short_name || ''),
                        (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
                }

                infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
                infowindow.open(map, marker);

                // Location details
                for (var i = 0; i < place.address_components.length; i++) {
                    console.log(place);

                    if (place.address_components[i].types[0] == 'locality') {
                        $('#city').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'country') {
                        $('#country').val(place.address_components[i].long_name);


                    }
                    if (place.address_components[i].types[0] == 'administrative_area_level_1') {
                        console.log(place.address_components[i].long_name);
                        $('#state').val(place.address_components[i].long_name);


                    }
                }


                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();
                $('#address').val(place.formatted_address);


                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());
            });
        }
        $(".RemoveBtngallary").click(function(e) {
            e.preventDefault();
            var id = $(this).data('rowid');
            Swal.fire({
                title: 'Are You Sure Want to Remove This Image',
                icon: 'error',
                showDenyButton: true,

                confirmButtonText: 'Yes',
                denyCanceButtonText: `No`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('property.removeGalleryImage') }}",

                        type: "POST",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            "id": id
                        },
                        success: function(response) {

                            if (response.error == false) {
                                Toastify({
                                    text: 'Image Delete Successful',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
                                }).showToast();
                                $("#" + id).html('');
                            } else if (response.error == true) {
                                Toastify({
                                    text: 'Something Wrong !!!',
                                    duration: 6000,
                                    close: !0,
                                    backgroundColor: '#dc3545' //"linear-gradient(to right, #dc3545, #96c93d)"
                                }).showToast()
                            }
                        },
                        error: function(xhr) {}
                    });
                }
            })


        });

        $(document).on('click', '#filepond_3d', function(e) {

            $('.3d_img').hide();
        });
        $(document).on('click', '#filepond_title', function(e) {

            $('.title_img').hide();
        });

        jQuery(document).ready(function() {

            initMap();

            $('#map').append('<iframe src="https://maps.google.com/maps?q=' + $('#latitude').val() + ',' + $(
                    '#longitude').val() +
                '&hl=en&amp;z=18&amp;output=embed" height="375px" width="800px"></iframe>');


        });
        $(document).ready(function() {
            $('.parsley-error filled,.parsley-required').attr("aria-hidden", "true");
            $('.parsley-error filled,.parsley-required').hide();

        });
    </script>


    <style>
        .error-message {
            color: red;
            margin-top: 5px;
            font-size: 15px;
        }
    </style>
@endsection
