<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Property;
use App\Models\UserInterest;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewApiController extends Controller
{
    public function appHome(Request $request)
    {
        $data['recent_added'] = $this->recentAdded($request);
        $data['recommendation'] = $this->recommendation($request);
        $data['installments'] = $this->installments($request);
        $data['top_rated'] = $this->topRated($request);
        $data['most_liked'] = $this->mostLiked($request);
        $data['caysh'] = $this->caysh($request);
        $data['promoted'] = $this->promoted($request);
        $data['categories'] = $this->categories($request);

        return response()->json($data);
    }

    function recentAdded($request)
    {
        $city = $request->city_id;

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category', function ($q) use ($request) {
                $q->where('caysh', 0);
            })
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->whereNotIn('id',$adv)
            ->orderBy('id', 'DESC');

        $totalCount = $query->count();

        $result = $query->take(6)->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function recommendation($request)
    {
        $current_user = $request->current_user;

        if(empty($current_user)){

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];

            return $response;
        }

        $user_interest = UserInterest::where('user_id', $current_user)->first();

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category', function ($q) use ($request) {
                $q->where('caysh', 0);
            })
            ->whereNotIn('id',$adv)
            ->orderBy('id', 'DESC');

        if($user_interest){
            if ($user_interest->manufacturer_ids != '') {
                $manufacturer_ids = explode(',', $user_interest->manufacturer_ids);
                $query = $query->whereIn('manufacturer_id', $manufacturer_ids);
            }

            if ($user_interest->model_ids != '') {

                $model_ids = explode(',', $user_interest->model_ids);
                $query = $query->whereIn('model_id', $model_ids);
            }

            if ($user_interest->city_ids != '') {

                $city_ids = explode(',', $user_interest->city_ids);
                $query = $query->whereIn('city_id', $city_ids);
            }

            if ($user_interest->area_ids != '') {

                $area_ids = explode(',', $user_interest->area_ids);
                $query = $query->whereIn('area_id', $area_ids);
            }

            if ($user_interest->year_range != '') {

                $max_year = explode(',', $user_interest->year_range)[1] ?? 3000;
                $min_year = explode(',', $user_interest->year_range)[0] ?? 1900;

                if (isset($max_year) && isset($min_year)) {

                    $query = $query->where(function ($query) use ($min_year, $max_year) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_year])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_year]);
                    });
                }
            }

            if ($user_interest->price_range != '') {

                $max_price = explode(',', $user_interest->price_range)[1] ?? 99999999999999;
                $min_price = explode(',', $user_interest->price_range)[0] ?? 0;

                if (isset($max_price) && isset($min_price)) {
                    $min_price = floatval($min_price);
                    $max_price = floatval($max_price);

                    $query = $query->where(function ($query) use ($min_price, $max_price) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_price])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_price]);
                    });
                }
            }
        }else{
            $query = $query->whereId(0);
        }

        $totalCount = $query->count();

        $result = $query
            ->take(6)
            ->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function installments($request)
    {
        $city = $request->city_id;

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category', function ($q) use ($request) {
                $q->where('caysh', 0);
            })
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->whereNotIn('id',$adv)
            ->where('installment', 1)
            ->orderBy('id', 'DESC');


        $totalCount = $query->count();

        $result = $query->take(6)
            ->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function topRated($request)
    {
        $city = $request->city_id;

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category', function ($q) use ($request) {
                $q->where('caysh', 0);
            })
            ->whereNotIn('id',$adv)
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->orderBy('total_click', 'DESC');


        $totalCount = $query->count();

        $result = $query->take(6)
            ->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function mostLiked($request)
    {
        $city = $request->city_id;

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category', function ($q) use ($request) {
                $q->where('caysh', 0);
            })
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->whereNotIn('id',$adv)
            ->withCount('favourite')
            ->orderBy('favourite_count', 'DESC');

        $totalCount = $query->count();

        $result = $query->take(6)
            ->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function caysh($request)
    {
        $city = $request->city_id;

        $current_user = $request->current_user;

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        if(empty($current_user)){

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];

            return $response;
        }

        $current_user_data = Customer::where('id', $current_user)->first();

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
        ->when($city,fn($q)=>$q->where('city_id', $city))
        ->whereNotIn('id',$adv)
        ->orderBy('id', 'DESC');


        if (isset($request->current_user) && $request->current_user != null) {
            if ($current_user_data->role == 1) {
                $query = $query->whereHas('category', function ($q) {
                    $q->where('caysh', 1);
                });
            } else {
                $response['error'] = false;
                $response['message'] = "No data found! Role != 1";
                $response['data'] = [];
                return ($response);
            }
        } else {
            $response['error'] = false;
            $response['message'] = "No data found! Not Logged In";
            $response['data'] = [];
            return ($response);
        }
        $totalCount = $query->count();

        $result = $query->take(6)->get();

        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function promoted($request)
    {
        $city = $request->city_id;

        $query = Property::with([
            'customer',
            'user',
            'category:id,category,category_ar,manufacturer,installment,caysh,image',
            'manufacturer:id,manufacturer,manufacturer_ar,image',
            'model:id,model',
            'year:id,year',
            'city:id,city,city_ar',
            'favourite',
            'parameters',
            'interested_users',
            'advertisement'
        ])
            ->whereHas('category')
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->orderBy('id', 'DESC');


        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get();

        $ad_arr = [];

        foreach ($adv as $ad) {
            array_push($ad_arr, $ad->property_id);
        }

        $query = $query->whereIn('id', $ad_arr);

        $totalCount = $query->count();

        $result = $query->take(6)->get();


        if (!$result->isEmpty()) {
            $property_details = get_property_details($result);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks'] = 0;
            $response['total'] = $totalCount;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
            $response['total'] = 0;
        }
        return ($response);
    }

    function categories($request)
    {
        $city = $request->city_id;

        $categories = Category::where('status', '1')->whereNotIn('id',[2])->get();

        $current_user = $request->current_user ?? 0;
        $current_user_data = Customer::where('id', $current_user)->first();

        $newData = [];

        $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get()->pluck('property_id');

        foreach ($categories as $i=>$cat) {
            if((!$current_user_data || $current_user_data->role != 1) && $cat->id == 13){continue;}
            $newData[$i]['id'] = $cat->id;
            $newData[$i]['category_ar'] = $cat->category_ar;
            $properties = Property::with([
                'customer',
                'user',
                'category:id,category,category_ar,manufacturer,installment,caysh,image',
                'manufacturer:id,manufacturer,manufacturer_ar,image',
                'model:id,model',
                'year:id,year',
                'city:id,city,city_ar',
                'favourite',
                'parameters',
                'interested_users',
                'advertisement'
            ])
            ->when($city,fn($q)=>$q->where('city_id', $city))
            ->whereNotIn('id',$adv)
            ->orderBy('id', 'DESC')
            ->where('category_id',$cat->id);

            $newData[$i]['total'] = $properties->count();

            $properties = $properties
            ->take(6)
            ->get();

            $newData[$i]['properties'] = get_property_details($properties);
        }

        return $newData;
    }

    function checkAvailable($customer) {
        $customer = Customer::findOrFail($customer);

        $currentPackage = $customer->currentPackage();

        $packagePropertyLimit = $currentPackage?->package?->property_limit ?? 0;
        $packageAdsLimit = $currentPackage?->package?->advertisement_limit ?? 0;

        $usedPackageAdsLimit = $currentPackge?->used_limit_for_property ?? $customer->usedPackageAdsLimit() ?? 0;
        $usedPackagePropertyLimit = $currentPackge?->used_limit_for_advertisement ?? $customer->usedPackagePropertyLimit() ?? 0;

        return response()->json([
            'can_add_property' => $packagePropertyLimit > $usedPackagePropertyLimit,
            'can_add_ads' => $packageAdsLimit > $usedPackageAdsLimit
        ]);
    }
}
