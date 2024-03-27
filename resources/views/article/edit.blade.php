@extends('layouts.main')

@section('title')
    {{ __('Edit Article') }}
@endsection

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
                            <a href="{{ route('article.index') }}" id="subURL">{{ __('View Article') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Edit') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <section class="section">
        {!! Form::open([
            'route' => ['article.update', $id],
            'method' => 'PATCH',
            'data-parsley-validate',
            'files' => true,
        ]) !!}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12 col-sm-12 form-group mandatory">
                                    {{ Form::label('title', __('Title'), ['class' => 'form-label col-12']) }}
                                    {{ Form::text('title', $list->title, ['class' => 'form-control ', 'placeholder' => 'Title', 'data-parsley-required' => 'true', 'id' => 'title']) }}
                                </div>
                                <div class="col-md-12 col-sm-12 form-group mandatory">
                                    {{ Form::label('category', __('Category'), ['class' => 'form-label col-12']) }}
                                    <select name="category" class="select2 form-select form-control-sm"
                                        data-parsley-minSelect='1' id="category" required>

                                        @foreach ($category as $row)
                                            <option value="{{ $row->id }}"
                                                {{ $list->id == $row->id ? 'selected' : '' }}>
                                                {{ $row->category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 col-sm-12 form-group mandatory">
                                    {{ Form::label('tag', __('Tag'), ['class' => 'form-label col-12']) }}
                                    {{ Form::text('tag', $list->tag_name, ['class' => 'form-control ', 'placeholder' => 'Title', 'data-parsley-required' => 'true', 'id' => 'title']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body" style="height: 290px;">
                        <div class="col-md-12 col-sm-12 form-group ">
                            {{ Form::label('image', __('Image'), ['class' => 'col-12 form-label']) }}
                            <input type="file" class="filepond" id="edit_image" name="image">
                        </div>
                        <div class="col-md-12 col-sm-12 ">
                            <div class="container img" style="width: 300px; max-height: 130px;">
                                <img src="{{ $list->image }}" style="max-width: 70%; max-height: 100%;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="col-md-12 col-sm-12 form-group mandatory">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label col-12']) }}
                            {{ Form::textarea('description', $list->description, ['class' => 'form-control ', 'id' => 'tinymce_editor', 'data-parsley-required' => 'true']) }}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="col-12 d-flex justify-content-end">

                            {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
@endsection

@section('script')
    <script>
        $(document).on('click', '#edit_image', function(e) {

            $('.img').hide();
        });
    </script>
@endsection
