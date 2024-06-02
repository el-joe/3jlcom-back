<form action="{{ url('adv-status-update') }}" class="form-horizontal" enctype="multipart/form-data" method="POST" data-parsley-validate >
    {{ csrf_field() }}
    <div class="row">
        <div class="col-sm-12 form-group">
            <label for="customer_id">الحاله</label>
            <select name="edit_adv_status" id="edit_adv_status" class="form-control" style="width: 100%">
                <option value='0' {{ isset($adv) && $adv->status == '0' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value='1' {{ isset($adv) && $adv->status == '1' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value='2' {{ isset($adv) && $adv->status == '2' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
            </select>
            <input type="hidden" name="id" id="id" value="{{ isset($adv) ? $adv->id : '' }}">
        </div>
        <div class="col-sm-12 form-group">
            <label for="customer_id">العميل</label>
            <select name="customer_id" id="customer_id" class="form-control" style="width: 100%" onchange="getProperties(event)">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ isset($adv) && $adv->customer_id == $customer->id ? 'selected' : '' }} advertisement="{{ isset($adv) ? $adv->id : '' }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-12 form-group">
            <label for="property_id">الاعلان</label>
            <select name="property_id" id="property_id" class="form-control" style="width: 100%">
                @if(isset($adv))
                    @foreach($customer->where('id',$adv->customer_id)->first()?->property ?? [] as $property)
                        <option value="{{ $property->id }}" {{ $adv->property_id == $property->id ? 'selected' : '' }}>{{ $property->title }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-sm-12 form-group">
            <label for="start_date">تاريخ البدايه</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ isset($adv) ? $adv->start_date : '' }}">
        </div>
        <div class="col-sm-12 form-group">
            <label for="end_date">تاريخ الانتهاء</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ isset($adv) ? $adv->end_date : '' }}">
        </div>
    </div>
    <div class="modal-footer" style="padding: 2% 0%">
        <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __('Close') }}</button>
        <button type="submit" class="btn btn-primary waves-effect waves-light">{{ __('Save') }}</button>
    </div>
</form>
