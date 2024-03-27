@extends('layouts.main')

@section('title')
    {{ __('Personalized') }}
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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="section">
        <div class="card">

            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list"
                            data-toggle="table" data-url="{{ url('getPersonalizedList') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-toolbar="#toolbar"
                            data-show-columns="true" data-show-refresh="true" data-fixed-columns="true"
                            data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false"
                            data-responsive="true" data-sort-name="id" data-sort-order="desc"
                            data-pagination-successively-size="3" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th scope="col" data-field="id" data-align="center" data-sortable="true">
                                        {{ __('ID') }}</th>
                                    <th scope="col" data-field="property_owner" data-align="center" data-sortable="false">
                                        {{ __('Owner Name') }}</th>
                                    <th scope="col" data-field="property_mobile" data-align="center" data-sortable="false">
                                        {{ __('Owner Mobile') }}</th>
                                    <th scope="col" data-field="offer" data-align="center" data-sortable="false">
                                        {{ __('Price') }} </th>
                                        
                                    @if (has_permissions('update', 'property') || has_permissions('delete', 'property'))
                                        <th scope="col" data-field="operate" data-align="center" data-events="actionEvents"
                                            data-sortable="false">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </section>
@endsection

@section('script')
    <script>
        $('#filter_status').on('change', function() {
            $('#table_list').bootstrapTable('refresh');

        })
        $(document).ready(function() {
            var params = new window.URLSearchParams(window.location.search);

            if (params.get('status') != 'null') {
                $('#status').val(params.get('status')).trigger('change');
            }
        });
        function queryParams(p) {
            return {
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                limit: p.limit,
                search: p.search,
            };
        }
    </script>
@endsection
