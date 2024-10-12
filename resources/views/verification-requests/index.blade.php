@extends('layouts.main')
@section('title') {{ __('Verification Requests') }} @endsection
@section('content')
<section class="section">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <table class="table-light" aria-describedby="mydesc" class='table-striped' id="table_list" data-toggle="table" data-url="{{ url('verification_request_list') }}" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200,All]" data-search="true" data-search-align="right" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true" data-fixed-columns="true" data-fixed-number="1" data-fixed-right-number="1" data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc" data-pagination-successively-size="3" data-query-params="queryParams">
                    <thead>
                        <tr>
                            <th scope="col" data-field="id" data-align="center" data-sortable="true"> {{ __('ID') }}</th>
                            <th scope="col" data-field="customer" data-align="center" data-sortable="true"> {{__('Customer') }}</th>
                            <th scope="col" data-field="status" data-align="center" data-sortable="false"> {{__('Status') }}</th>
                            <th scope="col" data-field="date" data-align="center" data-sortable="false"> {{ __('Date')}}</th>
                            <th scope="col" data-field="operator" data-align="center" data-sortable="false"> {{ __('Action')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

@endsection

@section('script')
<script>
    function queryParams(p) {

        return {
            sort: p.sort
            , order: p.order
            , offset: p.offset
            , limit: p.limit
            , search: p.search,

        };
    }

</script>
<script>


</script> @endsection

