<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\AssignParameters;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Modell;
use App\Models\Year;
use App\Models\Metadata;
use App\Models\City;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Favourite;
use App\Models\InterestedUser;
use App\Models\Language;
use App\Models\Notifications;
use App\Models\Package;
use App\Models\parameter;
use App\Models\Property;
use App\Models\PropertyImages;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Type;
use App\Models\Usertokens;
use App\Models\UserInterest;
use App\Models\User;
use App\Models\Chats;
use App\Models\report_reasons;
use App\Models\user_reports;
use App\Models\Payments;
use App\Models\Contactrequests;
use App\Models\UserPurchasedPackage;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Intervention\Image\ImageManagerStatic as Image;
use kornrunner\Blurhash\Blurhash;
use App\Libraries\Paypal;
use App\Libraries\Paypal_pro;
use App\Models\SubscriptionRequest;
use Exception;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Claims\Issuer;

class ApiController extends Controller
{
    function update_subscription()
    {
        $data = UserPurchasedPackage::where('user_id', Auth::id())->where('end_date', Carbon::now());
        if ($data) {
            $Customer = Customer::find(Auth::id());
            $Customer->subscription = 0;
            $Customer->update();
        }
    }

    //* START :: get_system_settings   *//
    public function get_system_settings(Request $request)
    {

        $result = '';

        $result =  Setting::select('type', 'data')->get();
        $data_arr = [];
        foreach ($result as $row) {
            /*if ($row->type == "place_api_key") {
                $publicKey = file_get_contents(base_path('public_key.pem')); // Load the public key

                $encryptedData = '';


                if (openssl_public_encrypt($row->data, $encryptedData, $publicKey)) {
                    // If encryption was successful, you can store or transmit $encryptedData as needed
                    $tempRow[$row->type] = base64_encode($encryptedData);
                } else {
                    // Handle encryption failure
                    // You can log an error or return an error response
                }

            } else {*/
                $tempRow[$row->type] = $row->data;
            //}
        }

        if (isset($request->user_id)) {

            $data = UserPurchasedPackage::where('modal_id', (string)$request->user_id)
                ->where('end_date', date('d'))->where('end_date', '!=', NULL)->get();

            $customer = Customer::select('id')->where('subscription', '1')
                ->with('user_purchased_package.package')->find((string)$request->user_id);

            if ($customer) {
                if (count($data)) {
                    $customer->subscription = 0;
                    $customer->update();
                }

                $tempRow['subscription'] = true;
                $tempRow['package'] = $customer;
                $tempRow['current_package'] = $customer->currentPackage()->load('package');
                $tempRow['current_package']['used_ads'] = $customer->usedPackageAdsLimit();
                $tempRow['current_package']['used_property'] = $customer->usedPackagePropertyLimit();
            } else {
                $tempRow['subscription'] = false;
            }
        }
        $language = Language::select('code', 'name')->get();
        $tempRow['demo_mode'] = env('DEMO_MODE');
        $tempRow['languages'] = $language;
        DB::enableQueryLog();

          $tempRow['min_price']= DB::table('propertys')
            ->selectRaw('MIN(CAST(price AS DECIMAL(10, 2))) as min_price')
            ->value('min_price');


      $tempRow['max_price']= DB::table('propertys')
            ->selectRaw('MAX(CAST(price AS DECIMAL(10, 2))) as min_price')
            ->value('min_price');

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $tempRow;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: Get System Setting   *//

    function sendSMS($phone,$code)
    {
        $curl = curl_init();

        $params = [
            'senderid'  =>  '3jlcom',
            'numbers'   =>  $phone,
            'accname'   =>  'ajlcom',
            'AccPass'   =>  'hB5rC2fP1qS1aE0x',
            'msg'       =>  "$code is your verification code for 3jlcom APP"
        ];

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://josmsservice.com/SMSServices/Clients/Prof/RestSingleSMS/SendSMS?' . http_build_query( $params ) ,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    function sendVerificationCodeToPhone(Request $request) {
        $request->validate([
            'mobile'=>'required'
        ]);

        $customer = Customer::where(function($q)use($request){
            $withoutPlus = str_replace('+','',$request->mobile);
            $withPlus = '+'.$withoutPlus;
            $q->where('mobile',$withoutPlus)->orWhere('mobile',$withPlus);
        })->first();

        $check = ($request->mobile == '123456789' || $request->mobile == '1234567899');

        $code = $check ? '123456' : rand(100000,999999);

        if(!$customer){
            $customer = new Customer();
            $customer->name = "";
            $customer->firebase_id = "";
            $customer->email = "";
            $customer->mobile = $request->mobile;
            $customer->address = "";
            $customer->logintype = 1;
            $customer->isActive = 1;
            $customer->about = "";
            $customer->instagram_link = "";
            $customer->twitter_link = "";
            $customer->facebook_link = "";
            $customer->pinterest_link = "";
            $customer->save();

            $start_date =  Carbon::now();
            $package = Package::find(1);

            if ($package && $package->status == 1) {
                $user_package = new UserPurchasedPackage();
                $user_package->modal()->associate($customer);
                $user_package->modal_id = (string)$customer->id;
                $user_package->package_id = 1;
                $user_package->start_date = $start_date;
                $user_package->end_date =  Carbon::now()->addDays($package->duration);
                $user_package->save();
                $customer->subscription = 1;
            }
        }

        $customer->verification_code = $code;
        $customer->save();
        if(!$check){
            $this->sendSMS($customer->phone,$customer->verification_code);
        }

        return response()->json([
            'status'=>true
        ]);
    }

    function verifyCode(Request $request) {
        $request->validate([
            'mobile'=>'required',
            'verification_code'=>'required'
        ]);

        $customer = Customer::where(function($q)use($request){
            $withoutPlus = str_replace('+','',$request->mobile);
            $withPlus = '+'.$withoutPlus;
            $q->where('mobile',$withoutPlus)->orWhere('mobile',$withPlus);
        })->where('verification_code',$request->verification_code)
        ->first();

        if(!$customer){
            return response()->json([
                'status'=>false,
                'msg'=>'invalid code'
            ]);
        }
        $customer = Customer::find($customer->id);
        $token = JWTAuth::fromUser($customer);

        return response()->json([
            'status'=>true,
            'token'=> $token,
            'data'=>$customer
        ]);
    }

    //* START :: user_signup   *//
    public function user_signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'verification_code' => 'required',
            'mobile' => 'required',
            'firebase_id'=>'required'
        ]);

        if (!$validator->fails()) {
            $type = 1;
            // $type = $request->type;
            $firebase_id = $request->firebase_id ?? \Str::random(15);
            $mobile = $request->mobile;
            // $code = $request->verification_code;

            $user = Customer::where(function($q)use($mobile){
                $withoutPlus = str_replace('+','',$mobile);
                $withPlus = '+'.$withoutPlus;
                $q->where('mobile',$withoutPlus)->orWhere('mobile',$withPlus);
            })->where('firebase_id', $firebase_id)->get();

            if ($user->isEmpty()) {
                $saveCustomer = new Customer();
                $saveCustomer->name = isset($request->name) ? $request->name : '';
                $saveCustomer->email = isset($request->email) ? $request->email : '';
                $saveCustomer->mobile = isset($request->mobile) ? Str::replaceFirst('96207', '9627', $request->mobile) : '';
                // $saveCustomer->profile = isset($request->profile) ? $request->profile : '';
                $saveCustomer->fcm_id = isset($request->fcm_id) ? $request->fcm_id : '';
                $saveCustomer->logintype = $type;
                $saveCustomer->address = isset($request->address) ? $request->address : '';
                $saveCustomer->firebase_id = isset($firebase_id) ? $firebase_id : '';
                $saveCustomer->isActive = '1';

                $saveCustomer->about = isset($request->about_me) ? $request->about_me : '';
                $saveCustomer->facebook_link = isset($request->facebook_link) ? $request->facebook_link : '';
                $saveCustomer->twitter_link = isset($request->twitter_link) ? $request->twitter_link : '';
                $saveCustomer->instagram_link = isset($request->instagram_link) ? $request->instagram_link : '';
                $saveCustomer->notification = 1;

                $destinationPath = public_path('images') . config('global.USER_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                // image upload

                if ($request->hasFile('profile')) {
                    // dd('in');
                    $profile = $request->file('profile');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                    $profile->move($destinationPath, $imageName);
                    $saveCustomer->profile = $imageName;
                } else {
                    $saveCustomer->profile = '';
                }
                /*if (isset($request->fcm_id)) {
                    $token_exist = Usertokens::where('fcm_id', $request->fcm_id)->get();
                    if (!count($token_exist)) {
                        $user_token = new Usertokens();
                        $user_token->customer_id = $saveCustomer->id;
                        $user_token->fcm_id = isset($request->fcm_id) ? $request->fcm_id : '';
                        $user_token->api_token = '';
                        $user_token->save();
                    }
                    $saveCustomer->fcm_id = ($request->fcm_id) ? $request->fcm_id : '';
                }*/

                $saveCustomer->save();

                $start_date =  Carbon::now();
                $package = Package::find(1);

                if ($package && $package->status == 1) {
                    $user_package = new UserPurchasedPackage();
                    $user_package->modal()->associate($saveCustomer);
                    $user_package->modal_id = (string)$saveCustomer->id;
                    $user_package->package_id = 1;
                    $user_package->start_date = $start_date;
                    $user_package->end_date =  Carbon::now()->addDays($package->duration);
                    $user_package->save();
                }

                $saveCustomer->subscription = 1;
                $saveCustomer->update();

                $response['error'] = false;
                $response['message'] = 'User Register Successfully';

                $credentials = Customer::find((string)$saveCustomer->id);
                $token = JWTAuth::fromUser($credentials);
                try {
                    if (!$token) {
                        $response['error'] = true;
                        $response['message'] = 'Login credentials are invalid.';
                    } else {
                        $credentials->api_token = $token;

                        $credentials->update();
                    }
                } catch (JWTException $e) {
                    $response['error'] = true;
                    $response['message'] = 'Could not create token.';
                }
                $response['token'] = $token;
                $response['data'] = $credentials;
            } else {
                $credentials = Customer::where(function($q)use($mobile){
                    $withoutPlus = ltrim($mobile, '+');
                    $withPlus = '+'.$withoutPlus;
                    $q->where('mobile',$withoutPlus)->orWhere('mobile',$withPlus);
                })->where('firebase_id', $firebase_id)->first();
                try {
                    $token = JWTAuth::fromUser($credentials);
                    if (!$token) {
                        $response['error'] = true;
                        $response['message'] = 'Login credentials are invalid.';
                    } else {
                        $credentials->api_token = $token;
                        /*if (isset($request->fcm_id)) {
                            $token_exist = Usertokens::where('fcm_id', $request->fcm_id)->get();
                            if (!count($token_exist)) {
                                $user_token = new Usertokens();
                                $user_token->customer_id = $credentials->id;
                                $user_token->fcm_id = isset($request->fcm_id) ? $request->fcm_id : '';
                                $user_token->api_token = '';
                                $user_token->save();
                            }
                            $credentials->fcm_id = ($request->fcm_id) ? $request->fcm_id : '';
                        }*/
                        $credentials->update();

                    }
                } catch (JWTException $e) {
                    $response['error'] = true;
                    $response['message'] = 'Could not create token.';
                }


                $response['error'] = false;
                $response['message'] = 'Login Successfully';
                $response['token'] = $token;
                $response['data'] = $credentials;
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Please fill all data and Submit';
        }
        return response()->json($response);
    }

    //* START :: update_profile   *//
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);


        if (!$validator->fails()) {
            $id = $request->userid;

            $customer =  Customer::find($id);

            if (!empty($customer)) {
                if (isset($request->name)) {
                    $customer->name = ($request->name) ? $request->name : '';
                }
                if (isset($request->email)) {
                    $customer->email = ($request->email) ? $request->email : '';
                }
                if (isset($request->mobile)) {
                    $customer->mobile = ($request->mobile) ? $request->mobile : '';
                }

                if (isset($request->instagram_link)) {
                    $customer->instagram_link = ($request->instagram_link) ? $request->instagram_link : '';
                }


                if (isset($request->twitter_link)) {
                    $customer->twitter_link = ($request->twitter_link) ? $request->twitter_link : '';
                }


                if (isset($request->facebook_link)) {
                    $customer->facebook_link = ($request->facebook_link) ? $request->facebook_link : '';
                }


                if (isset($request->pinterest_link)) {
                    $customer->pinterest_link = ($request->pinterest_link) ? $request->pinterest_link : '';
                }


                if (isset($request->fcm_id)) {
                    $token_exist = Usertokens::where('fcm_id', $request->fcm_id)->get();
                    if (!count($token_exist)) {
                        $user_token = new Usertokens();
                        $user_token->customer_id = $customer->id;
                        $user_token->fcm_id = isset($request->fcm_id) ? $request->fcm_id : '';
                        $user_token->api_token = '';
                        $user_token->save();
                    }
                    $customer->fcm_id = ($request->fcm_id) ? $request->fcm_id : '';
                }

                if (isset($request->address)) {
                    $customer->address = ($request->address) ? $request->address : '';
                }

                if (isset($request->firebase_id)) {
                    $customer->firebase_id = ($request->firebase_id) ? $request->firebase_id : '';
                }
                if (isset($request->notification)) {
                    $customer->notification = $request->notification;
                }

                $destinationPath = public_path('images') . config('global.USER_IMG_PATH');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                // image upload


                if ($request->hasFile('profile')) {
                    // dd('in');
                    $old_image = $customer->profile;

                    $profile = $request->file('profile');
                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                    if ($profile->move($destinationPath, $imageName)) {
                        $customer->profile = $imageName;
                        if ($old_image != '') {
                            if (file_exists(public_path('images') . config('global.USER_IMG_PATH') . $old_image)) {
                                unlink(public_path('images') . config('global.USER_IMG_PATH') . $old_image);
                            }
                        }
                    }
                }
                $customer->update();


                $response['error'] = false;
                $response['data'] = $customer;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: update_profile   *//

    //* START :: get_user_by_id   *//
    public function get_user_by_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        if (!$validator->fails()) {
            $id = $request->userid;

            $customer =  Customer::find($id);
            if (!empty($customer)) {
                $response['error'] = false;
                $response['data'] = $customer;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: get_user_by_id   *//

    //* START :: get_my_property   *//
    public function get_my_property(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $id = $request->id;

        $property = Property::with('customer')->with('user')->with('category:id,category,category_ar,manufacturer,installment,caysh,image')
        ->with('manufacturer:id,manufacturer,manufacturer_ar,image')->with('model:id,model')
        ->with('year:id,year')->with('city:id,city,city_ar')->with('favourite')->with('parameters')->with('interested_users');

        $property = $property->where('post_type', 1)->where('added_by', $current_user)->when($id, function ($query) use ($id) {
            return $query->where('id', $id);
        });
        $totalClicks = $property->where('post_type', 1)->where('added_by', $current_user)->sum('total_click');

        $total = $property->get()->count();

        $result = $property->skip($offset)->take($limit)->get();


        if (!$result->isEmpty()) {
            $property_details = get_property_details($result, $current_user);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks']=(double)$totalClicks;
            $response['total'] = $total;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return ($response);
    }
    //* END :: get_my_property   *//

    //* START :: get_property   *//
    public function get_property(Request $request)
    {
        $current_user=isset($request->current_user)?$request->current_user:'';
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        //$payload = JWTAuth::getPayload($this->bearerToken($request));
        //$current_user = (string)($payload['customer_id']);
        $current_user_data = Customer::where('id',$current_user)->first();
        DB::enableQueryLog();
        $property = Property::with('customer')->with('user')->with('category:id,category,category_ar,manufacturer,installment,caysh,image')
        ->with('manufacturer:id,manufacturer,manufacturer_ar,image')->with('model:id,model')
        ->with('year:id,year')->with('city:id,city,city_ar')->with('favourite')->with('parameters')->with('interested_users');

        $property_type = $request->property_type;  //0 : Buy 1:Rent
        $max_price = $request->max_price;
        $min_price = $request->min_price;
        $top_rated = $request->top_rated;
        $caysh = $request->caysh;
        $installment = $request->installment;

        $userid = $request->userid;
        $posted_since = $request->posted_since;
        $category_id = $request->category_id;
        $manufacturer_id = $request->manufacturer_id;
        $model_id = $request->model_id;
        $year_id = $request->year_id;
        $id = $request->id;
        $country = $request->country;
        $area = $request->area_id;
        $city = $request->city_id;
        $city_name = $request->city;
        $furnished = $request->furnished;
        $parameter_id = $request->parameter_id;
        $totalClicks=0;

        $parameters = $request->parameters;

        if (isset($caysh) && $caysh == 1) {
            if(isset($request->current_user) && $request->current_user != null){
                if ($current_user_data->role == 1) {
                    $property = $property->whereHas('category', function ($q) {
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
        } else {
            $property = $property->whereHas('category', function ($q) use($request) {
                    if($request->all != true){
                        $q->where('caysh', 0);
                    }
                });
        }

        if (isset($parameter_id)) {
            $property = $property->whereHas('parameters', function ($q) use ($parameter_id) {
                $q->where('parameter_id', $parameter_id);
            });
        }

        if(isset($parameters) && is_array($parameters)){
            $property = $property->whereHas('parameters', function ($q) use ($parameters) {
                foreach($parameters as $parameter){
                    $q->where('parameter_id', $parameter['id'])->where('value_ar', $parameter['value']);
                }
            });
        }

        if (isset($userid)) {
            $property = $property->where('post_type', 1)->where('added_by', $userid);
            $totalClicks = $property->where('post_type', 1)->where('added_by', $userid)->sum('total_click');
        } else {
            $property = $property->Where('status', 1);
        }

        if (isset($max_price) && isset($min_price)) {
            $property = $property->whereBetween('price', [$min_price, $max_price]);
        }

        if (isset($property_type)) {
            if ($property_type == 0 ||  $property_type == 2) {
                $property = $property->where('propery_type', $property_type);
            }
            if ($property_type == 1 ||  $property_type == 3) {
                $property = $property->where('propery_type', $property_type);
            }
        }

        if (isset($posted_since)) {
            // 0: last_week   1: yesterday
            if ($posted_since == 0) {
                $property = $property->whereBetween(
                    'created_at',
                    [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]
                );
            }
            if ($posted_since == 1) {
                $property =  $property->whereDate('created_at', Carbon::yesterday());
            }
        }

        if (isset($category_id)) {
            $property = $property->where('category_id', $category_id);
        }

        if (isset($manufacturer_id)) {
            $property = $property->where('manufacturer_id', $manufacturer_id);
        }

        if (isset($model_id)) {
            $property = $property->where('model_id', $model_id);
        }

        if (isset($year_id)) {
            $property = $property->where('year_id', $year_id);
        }

        if (isset($id)) {
            if(isset($request->get_simiilar)){
                $property = $property->where('id','!=', $id);
            }else{
                $property = $property->where('id', $id);
            }
        }

        if(isset($request->get_simiilar)){
            $currentProperty =  Property::where('id',$request->get_simiilar)->first();
            $property = $property->where('id','!=', $request->get_simiilar)->where('manufacturer_id',$currentProperty['manufacturer_id']);
        }

        if (isset($country)) {
            $property = $property->where('country', $country);
        }

        if (isset($request->title)) {
            $property = $property->where('title','LIKE', "%$reqest->title%");
        }

        if (isset($state)) {
            $property = $property->where('state', $state);
        }

        if (isset($city) && $city != '') {
            $property = $property->where('city_id', $city);
        }

        if (isset($request->promoted)) {
            $adv = Advertisement::select('property_id')->where('is_enable', 1)->where('status', 0)->get();

            $ad_arr = [];
            foreach ($adv as $ad) {

                array_push($ad_arr, $ad->property_id);
            }

            $property = $property->whereIn('id', $ad_arr);
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Promoted Else!";
            $response['data'] = [];
        }

        if (isset($request->users_promoted)) {
            $adv = Advertisement::select('property_id')->where('customer_id', $current_user)->get();

            $ad_arr = [];
            foreach ($adv as $ad) {
                array_push($ad_arr, $ad->property_id);
            }
            $property = $property->whereIn('id', $ad_arr);
        } else {
            $response['error'] = false;
            $response['message'] = "No data found! User Promoted Not Found";
            $response['data'] = [];
        }

        if (isset($request->promoted)) {
            if (!($property->Has('advertisement'))) {
                $response['error'] = false;
                $response['message'] = "No data found! Promoted not has Ads";
                $response['data'] = [];
                return ($response);
            }

            $property = $property->with('advertisement');
        }

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;

            $property = $property->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%$search%")->orwhere('address', 'LIKE', "%$search%")->orwhereHas('category', function ($query1) use ($search) {
                    $query1->where('category', 'LIKE', "%$search%")->orwhere('category_ar', 'LIKE', "%$search%");
                });
            });
        }

        if (empty($request->search)) {
            $property = $property;
        }

        if (isset($top_rated) && $top_rated == 1) {
            $property = $property->orderBy('total_click', 'DESC');
        }

        if (isset($installment)) {
            $property = $property->where('installment', 1);
        }

        if (!$request->most_liked && !$request->top_rated) {
            $property = $property->orderBy('id', 'DESC');
        }

        if ($request->most_liked) {
            $property = $property->withCount('favourite')
                ->orderBy('favourite_count', 'DESC');
        }

        $total = $property->get()->count();

        $result = $property->skip($offset)->take($limit)->get();


        if (!$result->isEmpty()) {
            $property_details
                = get_property_details($result, $current_user);

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total_clicks']=(double)$totalClicks;
            $response['total'] = $total;
            $response['data'] = $property_details;
        } else {

            $response['error'] = false;
            $response['message'] = "No data found! Is Empty";
            $response['data'] = [];
        }
        return ($response);
    }
    //* END :: get_property   *//

    //* START :: post_property   *//
    public function post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id'    => 'required',
            'category_id'   => 'required',
            'city_id'       => 'required',
            'area_id'       => 'required',
            'title'         => 'required',
            'description'   => 'required',
            'property_type' => 'required',
            'price'         => 'required',
            'latitude'      => 'required',
            'longitude'     => 'required',
            'title_image'   => 'required|file|max:3000|mimes:jpeg,png,jpg',
        ]);

        if (!$validator->fails()) {
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);

            $customer = Customer::find($current_user);
            $package = $customer->currentPackage()->load('package');


            $arr = 0;

            $prop_count = 0;
            if (!($package)) {
                $response['error'] = false;
                $response['message'] = 'Package not found';
                return response()->json($response);
            } else {

                if (!$package->package) {
                    $response['error'] = false;
                    $response['message'] = 'Package not found for add property';
                    return response()->json($response);
                }

                $prop_count = $package->package->property_limit;


                $status_settings = Setting::select('data')->where('type', 'require_approval')->first();
                $config = $status_settings['data'];

                $caysh_status_settings = Setting::select('data')->where('type', 'require_caysh_approval')->first();
                $config_caysh = $caysh_status_settings['data'];


                if (($package->used_limit_for_property) < ($prop_count) || $prop_count != 0) {

                    $validator = Validator::make($request->all(), [
                        'category_id' => 'required'
                    ]);

                    $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $Saveproperty = new Property();
                    $Saveproperty->category_id = $request->category_id;
                    $Saveproperty->manufacturer_id = (isset($request->manufacturer_id)) ? $request->manufacturer_id : '';
                    $Saveproperty->model_id = (isset($request->model_id)) ? $request->model_id : '';
                    $Saveproperty->year_id = (isset($request->year_id)) ? $request->year_id : '';

                    $Saveproperty->title = $request->title;
                    $Saveproperty->description = $request->description;
                    $Saveproperty->address = (isset($request->address)) ? $request->address : '';
                    $Saveproperty->client_address = (isset($request->client_address)) ? $request->client_address : '';

                    $Saveproperty->propery_type = $request->property_type;
                    $Saveproperty->price = $request->price;
                    $Saveproperty->installment = (isset($request->installment)) ? $request->installment : 0;
                    $Saveproperty->installment_price = (isset($request->installment_price)) ? $request->installment_price : '';
                    $Saveproperty->installment_down = (isset($request->installment_down)) ? $request->installment_down : '';
                    $Saveproperty->installment_type = (isset($request->installment_type)) ? $request->installment_type : '';

                    $Saveproperty->city_id = $request->city_id;
                    $Saveproperty->area_id = $request->area_id;
                    $Saveproperty->country = (isset($request->country)) ? $request->country : '';
                    $Saveproperty->state = (isset($request->state)) ? $request->state : '';
                    $Saveproperty->city_name = (isset($request->city)) ? $request->city : '';
                    $Saveproperty->latitude = (isset($request->latitude)) ? $request->latitude : '';
                    $Saveproperty->longitude = (isset($request->longitude)) ? $request->longitude : '';

                    $Saveproperty->added_by = $current_user;
                    $Saveproperty->status = $request->category_id == 13 ? ($config_caysh == "1" ? 0 : 1) : ($config == "1" ? 0 : 1);
                    $Saveproperty->video_link = (isset($request->video_link)) ? $request->video_link : "";
                    //$Saveproperty->dynamic_link = generate_dynamic_link("https://3jlcom.com/properties-deatils/53");

                    $Saveproperty->package_id = $request->package_id;
                    $Saveproperty->post_type = 1;
                    if ($request->hasFile('title_image')) {
                        $profile = $request->file('title_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);
                        $Saveproperty->title_image = $imageName;
                    } else {
                        $Saveproperty->title_image  = '';
                    }

                    // threeD_image
                    if ($request->hasFile('threeD_image')) {
                        $destinationPath = public_path('images') . config('global.3D_IMG_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        // $Saveproperty->threeD_image_hash = get_hash($request->file('threeD_image'));
                        $profile = $request->file('threeD_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);
                        $Saveproperty->threeD_image = $imageName;
                    } else {
                        $Saveproperty->threeD_image  = '';
                    }
                    $Saveproperty->save();
                    $package->used_limit_for_property =  $package->used_limit_for_property + 1;
                    $package->save();
                    $destinationPathforparam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }


                    if ($request->parameters) {
                        foreach ($request->parameters as $key => $parameter) {

                            // dd($parameter['value']);
                            $AssignParameters = new AssignParameters();

                            $AssignParameters->modal()->associate($Saveproperty);

                            $AssignParameters->parameter_id = $parameter['parameter_id'];

                            if ($request->hasFile('parameters.' . $key . '.value')) {

                                $profile = $request->file('parameters.' . $key . '.value');
                                $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                $profile->move($destinationPathforparam, $imageName);
                                $AssignParameters->value = $imageName;
                            } else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                                $ch = curl_init($parameter['value']);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $fileContents = curl_exec($ch);
                                curl_close($ch);

                                $filename = microtime(true) . basename($parameter['value']);
                                file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                $AssignParameters->value = $filename;
                            } else {
                                $AssignParameters->value = $parameter['value'];
                                $AssignParameters->value_ar = $parameter['value'];
                            }
                            $AssignParameters->save();
                        }
                    }

                    /// START :: UPLOAD GALLERY IMAGE
                    $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                    if (!is_dir($FolderPath)) {
                        mkdir($FolderPath, 0777, true);
                    }

                    $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $Saveproperty->id;
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    if ($request->hasfile('gallery_images')) {
                        foreach ($request->file('gallery_images') as $file) {
                            $name = time() . rand(1, 100) . '.' . $file->extension();
                            $file->move($destinationPath, $name);
                            $gallary_image = new PropertyImages();
                            $gallary_image->image = $name;
                            $gallary_image->propertys_id = $Saveproperty->id;
                            $gallary_image->save();
                        }
                    }
                    /// END :: UPLOAD GALLERY IMAGE

                    $result = Property::with('customer')->with('category:id,category,category_ar,manufacturer,installment,caysh,image')->with('favourite')->with('parameters')
                        ->with('interested_users')->where('id', $Saveproperty->id)->get();

                    if(isset($result[0])){
                        $property = $result[0];

                        UserInterest::all()->filter(function($q)use($property){
                            $manufacturers = explode(',',$q->manufacturer_ids);
                            $models = explode(',',$q->model_ids);
                            $yearRange = explode(',',$q->year_range);
                            $priceRange = explode(',',$q->price_range);
                            $cities = explode(',',$q->city_ids);
                            $areas = explode(',',$q->area_ids);

                            $manufacturerCheck = in_array($property->manufacturer_id,$this->filterArray($manufacturers));
                            $modelCheck = in_array($property->model_id,$this->filterArray($models));
                            $yearRangeCheck = in_array($property->year?->year, $this->filterArray($yearRange));
                            $priceRangeCheck = $property->price >= $priceRange[0] && $property->price <= $priceRange[1];
                            $citiesCheck = in_array($property->city_id, $this->filterArray($cities));
                            $areasCheck = in_array($property->area_id, $this->filterArray($areas));

                            if($manufacturerCheck && $modelCheck && $yearRangeCheck && $priceRangeCheck && $citiesCheck && $areasCheck) return true;
                        })->map(function($interest)use($property){
                            $customer = $interest->user_id;

                            Notifications::create([
                                'title' => __('New Offer'),
                                'message' => "هناك اعلان جديد لاقيها",
                                'image' => '',
                                'type' => '1',
                                'send_type' => '1',
                                'customers_id' => $customer,
                                'propertys_id' => $property->id
                            ]);
                    });
                    }

                    $property_details = get_property_details($result);

                    /*if(result['category']['id'] == '13'){

                    }*/

                    $response['error'] = false;
                    $response['message'] = 'Property Post Succssfully';
                    $response['data'] = $property_details;
                } else {
                    $response['error'] = false;
                    $response['message'] = 'Package Limit is over';
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    function filterArray($arr){
        return array_filter($arr,function($q){!empty($q);});
    }
    //* END :: post_property   *//

    //* START :: update_post_property   *//
    /// This api use for update and delete  property
    public function update_post_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'action_type' => 'required'
        ]);
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        if (!$validator->fails()) {
            $id = $request->id;
            $action_type = $request->action_type;

            $property = Property::where('added_by', (string)$current_user)->find($id);
            if (($property)) {
                // 0: Update 1: Delete
                if ($action_type == 0) {

                    $destinationPath = public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    if (isset($request->category_id)) {
                        $property->category_id = $request->category_id;
                    }

                    if (isset($request->title)) {
                        $property->title = $request->title;
                    }

                    if (isset($request->description)) {
                        $property->description = $request->description;
                    }

                    if (isset($request->address)) {
                        $property->address = $request->address;
                    }

                    if (isset($request->client_address)) {
                        $property->client_address = $request->client_address;
                    }

                    if (isset($request->propery_type)) {
                        $property->propery_type = $request->propery_type;
                    }

                    if (isset($request->price)) {
                        $property->price = $request->price;
                    }

                    if (isset($request->installment)) {
                        $property->installment = $request->installment;
                    }

                    if (isset($request->installment_price)) {
                        $property->installment_price = $request->installment_price;
                    }

                    if (isset($request->installment_down)) {
                        $property->installment_down = $request->installment_down;
                    }

                    if (isset($request->installment_type)) {
                        $property->installment_type = $request->installment_type;
                    }

                    if (isset($request->country)) {
                        $property->country = $request->country;
                    }

                    if (isset($request->state)) {
                        $property->state = $request->state;
                    }

                    if (isset($request->city)) {
                        $property->city_name = $request->city;
                    }

                    if (isset($request->status)) {
                        $property->status = $request->status;
                    }

                    if (isset($request->latitude)) {
                        $property->latitude = $request->latitude;
                    }

                    if (isset($request->longitude)) {
                        $property->longitude = $request->longitude;
                    }


                    /*if ($request->hasFile('title_image')) {
                        $profile = $request->file('title_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath, $imageName);


                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') .  $property->title_image)) {
                                unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                            }
                        }
                        $property->title_image = $imageName;
                    }*/

                    /*if ($request->hasFile('threeD_image')) {
                        $destinationPath1 = public_path('images') . config('global.3D_IMG_PATH');
                        if (!is_dir($destinationPath1)) {
                            mkdir($destinationPath1, 0777, true);
                        }
                        $profile = $request->file('threeD_image');
                        $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                        $profile->move($destinationPath1, $imageName);


                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.3D_IMG_PATH') .  $property->title_image)) {
                                unlink(public_path('images') . config('global.3D_IMG_PATH') . $property->title_image);
                            }
                        }
                        $property->threeD_image = $imageName;
                    }*/
                    if ($request->parameters) {
                        $destinationPathforparam = public_path('images') . config('global.PARAMETER_IMAGE_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }
                        // print_r($request->parameters);

                        foreach ($request->parameters as $key => $parameter) {
                            // print_r($parameter);
                            // echo $property->id;
                            // return false;
                            $AssignParameters = AssignParameters::where('modal_id', $property->id)->where('parameter_id', $parameter['parameter_id'])->pluck('id');
                            // echo $AssignParameters[0] . 'idddd';
                            $update_data = AssignParameters::find($AssignParameters[0]);
                            if ($update_data) {
                                // print_r($update_data->toArray());
                                // $AssignParameters->modal()->associate($property);

                                // $AssignParameters->parameter_id = $parameter['parameter_id'];

                                if ($request->hasFile('parameters.' . $key . '.value')) {

                                    $profile = $request->file('parameters.' . $key . '.value');
                                    $imageName = microtime(true) . "." . $profile->getClientOriginalExtension();
                                    $profile->move($destinationPathforparam, $imageName);
                                    $update_data->value = $imageName;
                                }
                                // if (isUrl($parameter['value'])) {
                                else if (filter_var($parameter['value'], FILTER_VALIDATE_URL)) {
                                    // dd('stop');


                                    $ch = curl_init($parameter['value']);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    $fileContents = curl_exec($ch);
                                    curl_close($ch);

                                    // $fileContents = file_get_contents($parameter['value']);
                                    $filename
                                        = microtime(true) . basename($parameter['value']);
                                    // dd($filename);
                                    file_put_contents($destinationPathforparam . '/' . $filename, $fileContents);
                                    $update_data->value = $filename;
                                } else {
                                    $update_data->value = $parameter['value'];
                                    $update_data->value_ar = $parameter['value'];
                                }


                                $update_data->save();
                            }
                        }

                        // $AssignParameters->save();
                    }

                    $property->update();
                    $update_property = Property::with('customer')->with('category:id,category,category_ar,image')->with('favourite')->with('parameters')->with('interested_users')->where('id', $request->id)->get();


                    /// START :: UPLOAD GALLERY IMAGE

                    $FolderPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH');
                    if (!is_dir($FolderPath)) {
                        mkdir($FolderPath, 0777, true);
                    }

                    $destinationPath = public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . "/" . $property->id;
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    if ($request->hasfile('gallery_images')) {
                        foreach ($request->file('gallery_images') as $file) {
                            $name = time() . rand(1, 100) . '.' . $file->extension();
                            $file->move($destinationPath, $name);

                            PropertyImages::create([
                                'image' => $name,
                                'propertys_id' => $property->id,
                            ]);
                        }
                    }

                    /// END :: UPLOAD GALLERY IMAGE
                    $payload = JWTAuth::getPayload($this->bearerToken($request));
                    $current_user = (string)($payload['customer_id']);
                    $property_details = get_property_details($update_property, $current_user);
                    $response['error'] = false;
                    $response['message'] = 'Property Update Succssfully';
                    $response['data'] = $property_details;
                } elseif ($action_type == 1) {
                    if ($property->delete()) {

                        $chat = Chats::where('property_id', $property->id);
                        if ($chat) {
                            $chat->delete();
                        }

                        $enquiry = PropertysInquiry::where('propertys_id', $property->id);
                        if ($enquiry) {
                            $enquiry->delete();
                        }

                        $slider = Slider::where('propertys_id', $property->id);
                        if ($slider) {
                            $slider->delete();
                        }


                        $notifications = Notifications::where('propertys_id', $property->id);
                        if ($notifications) {
                            $notifications->delete();
                        }

                        if ($property->title_image != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image)) {
                                unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                            }
                        }
                        foreach ($property->gallery as $row) {
                            if (PropertyImages::where('id', $row->id)->delete()) {
                                if ($row->image_url != '') {
                                    if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image)) {
                                        unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image);
                                    }
                                }
                            }
                        }
                        rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id);

                        Notifications::where('propertys_id', $id)->delete();


                        $slider = Slider::where('propertys_id', $id)->get();

                        foreach ($slider as $row) {
                            $image = $row->image;

                            if (Slider::where('id', $row->id)->delete()) {
                                if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                                    unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                                }
                            }
                        }

                        $response['error'] = false;
                        $response['message'] =  'Delete Successfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'something wrong';
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'No Data Found';
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: update_post_property   *//

    //* START :: get_slider   *//
    public function get_slider(Request $request)
    {
        $slider = Slider::select('id', 'image', 'sequence', 'category_id', 'propertys_id')
            ->with('property')
            ->orderBy('sequence', 'ASC')->get()->map(function($row){
                if(filter_var($row->image, FILTER_VALIDATE_URL) === false){
                    $image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.SLIDER_IMG_PATH') . $row->image : $row->property?->title_image;
                }else{
                    $image = $row->property?->title_image;
                }
                return [
                    'id' => $row->id,
                    'image' => $image,
                    'sequence' => $row->sequence,
                    'category_id' => $row->category_id,
                    'propertys_id' => $row->propertys_id,
                    'promoted' => true,
                    'offer'=> $row->property != null ? get_property_details([$row->property])[0] : null,
                ];
            });

        $advertisements = Advertisement::whereRaw('now() between start_date and end_date')
            ->with('property')
            ->where('is_enable', 1)->where('status', 0)->get()->map(function($row){
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.ADVERTISEMENT_IMAGE_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
                return [
                    'id' => $row->id,
                    'image' => $row->property?->title_image,
                    'sequence' => 0,
                    'category_id' => $row->property?->category_id,
                    'propertys_id' => $row->property_id,
                    'promoted' => true,
                    'offer'=> $row->property != null ? get_property_details([$row->property])[0] : null,
                ];
            });

            $rows = $slider->merge($advertisements)->sortBy('sequence')->values()->all();

            if(count($rows) > 0){
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['data'] = $rows;
            }else{
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }

        return response()->json($response);
    }
    //* END :: get_slider   *//

    //* START :: get_categories   *//
    public function get_categories(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $categories = Category::select('id', 'category', 'category_ar', 'manufacturer', 'installment', 'caysh', 'image', 'parameter_types')->where('status', '1')->withCount(['properties' => function ($q) {
            $q->where('status', 1);
        }]);

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $categories->where('category', 'LIKE', "%$search%")->orWhere('category_ar', 'LIKE', "%$search%");
        }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $categories->where('id', '=', $id);
        }

        if (isset($request->find) && !empty($request->find)) {
            $find = $request->find;
            $categories->where('find', $find);
        }

        $total = $categories->get()->count();
        $result = $categories->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            foreach ($result as $row) {
                $row->parameter_types = parameterTypesByCategory($row->id);
            }

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: get_categories   *//

    //* START :: get_manufacturers   *//
    public function get_manufacturers(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $manufacturers = Manufacturer::select('id', 'manufacturer','manufacturer_ar', 'image')->where('status', '1')->withCount(['properties' => function ($q) {
            $q->where('status', 1)->whereHas('category', function ($q) {
                        $q->where('caysh', 0);
                    });
        }]);

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $manufacturers->where('manufacturer', 'LIKE', "%$search%")->orWhere('manufacturer_ar', 'LIKE', "%$search%");
        }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $manufacturers->where('id', '=', $id);
        }

        $total = $manufacturers->get()->count();
        $result = $manufacturers->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: get_manufacturers   *//

    //* START :: get_models   *//
    public function get_models(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'manufacturer_id' => 'required',
        ]);


        if (!$validator->fails()) {
            $manufacturer = $request->manufacturer_id;
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            $models = Modell::select('id', 'model')->where('manufacturer_id', $manufacturer)->where('status', '1');

            if (isset($request->search) && !empty($request->search)) {
                $search = $request->search;
                $models->where('model', 'LIKE', "%$search%");
            }

            if (isset($request->id) && !empty($request->id)) {
                $id = $request->id;
                $models->where('id', '=', $id);
            }

            $total = $models->get()->count();
            $result = $models->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();




            if (!$result->isEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $result;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }

        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    //* END :: get_models   *//

    //* START :: get_years   *//
    public function get_years(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'model_id' => 'required',
        ]);


        if (!$validator->fails()) {
            $model = $request->model_id;
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            $year = Year::select('id', 'year')->where('status', '1');

            if (isset($request->search) && !empty($request->search)) {
                $search = $request->search;
                $year->where('year', 'LIKE', "%$search%");
            }

            if (isset($request->id) && !empty($request->id)) {
                $id = $request->id;
                $year->where('id', '=', $id);
            }

            $total = $year->get()->count();
            $result = $year->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

            if (!$result->isEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $result;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }

        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    //* END :: get_years   *//

    //* START :: get_cities   *//
    public function get_cities(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $city = City::select('id', 'city','city_ar')->where('status', '1');

        if (isset($request->search) && !empty($request->search)) {
            $search = $request->search;
            $city->where('city', 'LIKE', "%$search%")->orWhere('city_ar', 'LIKE', "%$search%");
        }

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $city->where('id', '=', $id);
        }

        $total = $city->get()->count();
        $result = $city->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }
    //* END :: get_cities   *//

    //* START :: get_areas   *//
    public function get_areas(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'city_id' => 'required',
        ]);


        if (!$validator->fails()) {
            $city = $request->city_id;
            $offset = isset($request->offset) ? $request->offset : 0;
            $limit = isset($request->limit) ? $request->limit : 10;

            $areas = Area::select('id', 'area', 'area_ar')->where('city_id', $city)->where('status', '1');

            if (isset($request->search) && !empty($request->search)) {
                $search = $request->search;
                $areas->where('area', 'LIKE', "%$search%")->orWhere('area_ar', 'LIKE', "%$search%");
            }

            if (isset($request->id) && !empty($request->id)) {
                $id = $request->id;
                $areas->where('id', '=', $id);
            }

            $total = $areas->get()->count();
            $result = $areas->orderBy('sequence', 'ASC')->skip($offset)->take($limit)->get();

            if (!$result->isEmpty()) {
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $result;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }

        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    //* END :: get_areas   *//

    //* START :: remove_post_images   *//
    public function remove_post_images(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if (!$validator->fails()) {
            $id = $request->id;
            $getImage = PropertyImages::where('id', $id)->first();
            $image = $getImage->image;
            $propertys_id =  $getImage->propertys_id;

            if (PropertyImages::where('id', $id)->delete()) {
                if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image)) {
                    unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id . "/" . $image);
                }
                $response['error'] = false;
            } else {
                $response['error'] = true;
            }

            $countImage = PropertyImages::where('propertys_id', $propertys_id)->get();
            if ($countImage->count() == 0) {
                rmdir(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $propertys_id);
            }

            $response['error'] = false;
            $response['message'] = 'Property Post Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: remove_post_images   *//

    //* START :: set_property_inquiry   *//
    public function set_property_inquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action_type' => 'required',
        ]);

        if (!$validator->fails()) {
            $action_type = $request->action_type;  ////0: add   1:update


            if ($action_type == 0) {
                //add inquiry
                $validator = Validator::make($request->all(), [
                    'property_id' => 'required',
                    'offer' => 'required',
                ]);
                $payload = JWTAuth::getPayload($this->bearerToken($request));
                $current_user = (string)($payload['customer_id']);
                if (!$validator->fails()) {
                    $PropertysInquiry = PropertysInquiry::where('propertys_id', $request->property_id)->where('customers_id', $current_user)->first();
                    if (empty($PropertysInquiry)) {

                        PropertysInquiry::create([
                            'propertys_id' => $request->property_id,
                            'customers_id' => $current_user,
                            'offer' => $request->offer,
                            'status'  => '0'
                        ]);

                        $Property = Property::with('customer')->find($request->property_id);
                        if (count($Property->customer) > 0) {

                            if ($Property->customer[0]->fcm_id != '' && $Property->customer[0]->notification == 1) {

                                //START :: Send Notification To Customer
                                $fcm_ids = array();
                                $customer_id = Customer::where('id', (string)$Property->customer[0]->id)->where('isActive', '1')->where('notification', 1)->get();
                                if (count($customer_id)) {
                                    $user_token = Usertokens::where('customer_id', $Property->customer[0]->id)->select('id', 'fcm_id')->get()->pluck('fcm_id')->toArray();
                                }

                                $fcm_ids[] = $user_token;
                                $msg = "";
                                if (!empty($fcm_ids)) {
                                    $msg = __('New Offer Added on You Car Ad');
                                    $registrationIDs = $fcm_ids[0];
                                    $fcmMsg = array(
                                        'title' => __('New Offer'),
                                        'message' => $msg,
                                        'type' => 'property_inquiry',
                                        'body' => $msg,
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                        'sound' => 'default',
                                        'id' => $Property->id,
                                    );
                                    send_push_notification($registrationIDs, $fcmMsg);
                                }
                                //END ::  Send Notification To Customer

                                Notifications::create([
                                    'title' => __('New Offer'),
                                    'message' => $msg,
                                    'image' => '',
                                    'type' => '1',
                                    'send_type' => '1',
                                    'customers_id' => $Property->customer[0]->id,
                                    'propertys_id' => $Property->id
                                ]);
                            }

                            try{
                                $chatData = [
                                    'property_id' => $request->property_id,
                                    'sender_id' => $current_user,
                                    'receiver_id' => $Property->customer[0]->id,
                                    'message'=> 'لديك عرض جديد علي اعلانك ' . $request->offer . ' دينار'
                                ];

                                $chat = Chats::create($chatData);
                            }catch(\Exception $e){
                                //dd($e->getMessage());
                            }
                        }
                        $response['error'] = false;
                        $response['message'] = 'Inquiry Send Succssfully';
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Request Already Submitted';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Please fill all data and Submit";
                }
            } elseif ($action_type == 1) {
                //update inquiry
                $validator = Validator::make($request->all(), [
                    'id' => 'required',
                    'status' => 'required',

                ]);

                if (!$validator->fails()) {
                    $id = $request->id;
                    $propertyInquiry = PropertysInquiry::find($id);
                    $propertyInquiry->status = $request->status;
                    $propertyInquiry->update();


                    $response['error'] = false;
                    $response['message'] = 'Inquiry Update Succssfully';
                } else {
                    $response['error'] = true;
                    $response['message'] = "Please fill all data and Submit";
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }



        return response()->json($response);
    }
    //* END :: set_property_inquiry   *//

    //* START :: get_notification_list   *//
    public function get_notification_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        if (!$validator->fails()) {
            $id = $request->userid;

            $Notifications =  Notifications::with('property')->whereRaw("FIND_IN_SET($id,customers_id)")->orwhere('send_type', '1')->orderBy('id', 'DESC')->get();


            if (!$Notifications->isEmpty()) {
                for ($i = 0; $i < count($Notifications); $i++) {
                    $Notifications[$i]->created = $Notifications[$i]->created_at->diffForHumans();
                    $Notifications[$i]->image  = ($Notifications[$i]->image != '') ? url('') . config('global.IMG_PATH') . config('global.NOTIFICATION_IMG_PATH') . $Notifications[$i]->image : '';
                    $Notifications[$i]->property = $Notifications[$i]->property ? get_property_details(collect([$Notifications[$i]->property]))[0] : null;
                }
                $response['error'] = false;
                $response['data'] = $Notifications;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: get_notification_list   *//

    //* START :: get_property_inquiry   *//
    public function get_property_inquiry(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        $propertyInquiry = PropertysInquiry::with('property')->where('customers_id', $current_user);
        $total = $propertyInquiry->get()->count();
        $result = $propertyInquiry->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();


        $rows = array();
        $tempRow = array();
        $count = 1;

        if (!$result->isEmpty()) {

            foreach ($result as $key => $row) {
                $tempRow['id'] = $row->id;
                $tempRow['propertys_id'] = $row->propertys_id;
                $tempRow['offer'] = $row->offer;
                $tempRow['customers'] = $row->customers_id;

                $customer = $row->customer->first();

                $tempRow['status'] = $row->status;
                $tempRow['created_at'] = $row->created_at;
                $tempRow['property']['id'] = $row['property']->id;
                $tempRow['property']['title'] = $row['property']->title;
                $tempRow['property']['price'] = $row['property']->price;
                $tempRow['property']['installment'] = $row['property']->installment;
                $tempRow['property']['installment_price'] = $row['property']->installment_price;
                $tempRow['property']['installment_down'] = $row['property']->installment_down;
                $tempRow['property']['installment_type'] = $row['property']->installment_type;

                if ($customer && $row->customers_id != 0) {

                    $customerData['id'] = $customer->id;
                    $customerData['name'] = $customer->name;
                    $customerData['email'] = $customer->email;
                    $customerData['mobile'] = $customer->mobile;
                    $customerData['profile'] = $customer->profile;
                    $customerData['address'] = $customer->address;
                    $customerData['role'] = $customer->role;

                    if ($customer->isVerified) {
                    $customerData['isVerified'] = true;
                    } else {
                        $customerData['isVerified'] = false;
                    }

                    if ($customer->isActive) {
                    $customerData['isActive'] = true;
                    } else {
                        $customerData['isActive'] = false;
                    }

                    $customerData['about'] = $customer->about;
                    $customerData['instagram_link'] = $customer->instagram_link;
                    $customerData['twitter_link'] = $customer->twitter_link;
                    $customerData['facebook_link'] = $customer->facebook_link;
                    $customerData['customertotalpost'] = $customer->customertotalpost;

                    $tempRow['property']['customer'] = $customerData;

                } else if ($row->customers_id == 0) {

                    $mobile = Setting::where('type', 'company_tel1')->pluck('data');
                    $email = Setting::where('type', 'company_email')->pluck('data');
                    $address = Setting::where('type', 'company_address')->pluck('data');
                    $logo = Setting::where('type', 'favicon_icon')->pluck('data');
                    $about = Setting::where('type', 'company_address')->pluck('data');

                    $adminData['id'] = 0;
                    $adminData['name'] = "3jlcom - عجلكم";
                    $adminData['email'] = $email[0];
                    $adminData['mobile'] = $mobile[0];
                    $adminData['profile'] = url('') . config('global.LOGO_PATH') . $logo[0];
                    $adminData['address'] = $address[0];
                    $adminData['role'] = 1;
                    $adminData['isActive'] = true;
                    $adminData['isVerified'] = true;
                    $adminData['about'] = $about[0];
                    $adminData['instagram_link'] = $address[0];
                    $adminData['twitter_link'] = $address[0];
                    $adminData['facebook_link'] = $address[0];
                    $adminData['customertotalpost'] = Property::where('added_by', $row->added_by)->get()->count();

                    $tempRow['property']['customer']= $adminData;
                }

                $tempRow['property']['category'] = $row['property']->category;
                $tempRow['property']['manufacturer'] = $row['property']->manufacturer;
                $tempRow['property']['model'] = $row['property']->model;
                $tempRow['property']['year'] = $row['property']->year;
                $tempRow['property']['city'] = $row['property']->city;
                $tempRow['property']['area'] = $row['property']->area;

                $promoted = Advertisement::where('property_id', $row->id)->first();

                if ($promoted) {
                    $tempRow['property']['promoted'] = true;
                } else {
                    $tempRow['property']['promoted'] = false;
                }

                $tempRow['property']['inquiry'] = true;

                $tempRow['property']['description'] = $row['property']->description;
                $tempRow['property']['address'] = $row['property']->address;
                $tempRow['property']['client_address'] = $row['property']->client_address;
                $tempRow['property']['propery_type'] = ($row['property']->propery_type == '0') ? 'Sell' : 'Rent';
                $tempRow['property']['title_image'] = $row['property']->title_image;

                $tempRow['property']['threeD_image'] = $row['property']->threeD_image;

                $tempRow['property']['post_created'] = $row['property']->created_at->diffForHumans();
                Carbon::setLocale('ar');
                $tempRow['property']['post_created_ar'] = $row['property']->created_at->diffForHumans();
                $tempRow['property']['gallery'] = $row['property']->gallery;
                $tempRow['property']['total_view'] = $row['property']->total_click;
                $tempRow['property']['status'] = $row['property']->status;
                $tempRow['property']['state'] = $row['property']->state;
                $tempRow['property']['city_name'] = $row['property']->city_name;
                $tempRow['property']['country'] = $row['property']->country;
                $tempRow['property']['latitude'] = $row['property']->latitude;
                $tempRow['property']['longitude'] = $row['property']->longitude;
                $tempRow['property']['inquired_users'] = [];
                $tempRow['property']['added_by'] = $row['property']->added_by;
                foreach ($row->property->assignParameter as $key => $res) {

                    $tempRow['property']["parameters"][$key] = $res->parameter;

                    $tempRow['property']["parameters"][$key]["value"] = $res->value;
                    $tempRow['property']["parameters"][$key]["value_ar"] = $res->value_ar;
                }


                $rows[] = $tempRow;
                // $parameters[] = $arr;
                $count++;
            }

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }


        return response()->json($response);
    }
    //* END :: get_property_inquiry   *//

    //* START :: set_property_total_click   *//
    public function set_property_total_click(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',

        ]);

        if (!$validator->fails()) {
            $property_id = $request->property_id;
            $property = Property::find($property_id);
            $property->increment('total_click');

            $response['error'] = false;
            $response['message'] = 'Update Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }

        return response()->json($response);
    }
    //* END :: set_property_total_click   *//

    //* START :: delete_user   *//
    public function delete_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        if (!$validator->fails()) {
            $userid = $request->userid;
            Customer::find($userid)->delete();
            Property::where('added_by', $userid)->delete();
            PropertysInquiry::where('customers_id', $userid)->delete();

            $response['error'] = false;
            $response['message'] = 'Delete Succssfully';
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }
    //* END :: delete_user   *//

    public function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }

    //*START :: add favoutite *//
    public function add_favourite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'property_id' => 'required',
        ]);

        if (!$validator->fails()) {
            //add favourite
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);
            if ($request->type == 1) {


                $fav_prop = Favourite::where('user_id', $current_user)->where('property_id', $request->property_id)->get();

                if (count($fav_prop) > 0) {
                    $response['error'] = false;
                    $response['message'] = "Property already add to favourite";
                    return response()->json($response);
                }
                $favourite = new Favourite();
                $favourite->user_id = $current_user;
                $favourite->property_id = $request->property_id;
                $favourite->save();
                $response['error'] = false;
                $response['message'] = "Property add to Favourite add successfully";
            }
            //delete favourite
            if ($request->type == 0) {
                Favourite::where('property_id', $request->property_id)->where('user_id', $current_user)->delete();

                $response['error'] = false;
                $response['message'] = "Property remove from Favourite  successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }


        return response()->json($response);
    }

    public function get_articles(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $article = Article::select('id', 'image', 'title', 'description', 'category_id', 'created_at');

        if (isset($request->category_id)) {
            $category_id = $request->category_id;
            if ($category_id == 0) {
                $article = $article->where('category_id', '');
            } else {

                $article = $article->where('category_id', $category_id);
            }
        }

        if (isset($request->id)) {
                if (isset($request->get_simiilar)) {
                $article = $article->where('id', '!=', $request->id);
            } else{
            $article = $article->where('id', $request->id);
            }
        }

        $total = $article->get()->count();
        $result = $article->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();
        if (!$result->isEmpty()) {
            foreach ($article as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.ARTICLE_IMG_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function store_advertisement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'property_id' => 'required',
            'package_id' => 'required',
        ]);

        if (!$validator->fails()) {
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);

            $userpackage = UserPurchasedPackage::where('modal_id', $current_user)->with(['package' => function ($q) {
                $q->select('id', 'property_limit', 'advertisement_limit')->where('advertisement_limit', '!=', NULL);
            }])->latest()->first();


            // $arr = 0;

            // $prop_count = 0;
            if (!($userpackage)) {
                $response['error'] = false;
                $response['message'] = 'Package not found';
                return response()->json($response);
            } else {
                if (!$userpackage->package) {
                    $response['error'] = false;
                    $response['message'] = 'Package not found for add property';
                    return response()->json($response);
                }
                $advertisement_count = $userpackage->package->advertisement_limit;

                // $arr = $userpackage->id;

                // $advertisement_limit = Advertisement::where('customer_id', $current_user)->where('package_id', $request->package_id)->get();

                if (($userpackage->used_limit_for_advertisement < ($advertisement_count) && $advertisement_count != 0)) {

                    $payload = JWTAuth::getPayload($this->bearerToken($request));
                    $current_user = (string)($payload['customer_id']);

                    $package = Package::where('advertisement_limit', '!=', NULL)->find($request->package_id);

                    $adv = new Advertisement();

                    $adv->start_date = Carbon::now();
                    if ($package) {
                        if (isset($request->end_date)) {
                            $adv->end_date = $request->end_date;
                        } else {
                            $adv->end_date = Carbon::now()->addDays($package->duration);
                        }

                        $adv->package_id = $package->id;

                        $adv->type = $request->type;
                        $adv->property_id = $request->property_id;
                        $adv->customer_id = $current_user;
                        $adv->is_enable = true;
                        $adv->status = 0;

                        $destinationPath = public_path('images') . config('global.ADVERTISEMENT_IMAGE_PATH');
                        if (!is_dir($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }

                        if ($request->type == 'Slider') {
                            $destinationPath_slider = public_path('images') . config('global.SLIDER_IMG_PATH');
                            if (!is_dir($destinationPath_slider)) {
                                mkdir($destinationPath_slider, 0777, true);
                            }
                            $slider = new Slider();
                            if ($request->hasFile('image')) {
                                $file = $request->file('image');
                                $name = time() . rand(1, 100) . '.' . $file->extension();
                                $file->move($destinationPath_slider, $name);
                                $sliderimageName = microtime(true) . "." . $file->getClientOriginalExtension();
                                $slider->image = $sliderimageName;
                            } else {
                                $slider->image = '';
                            }
                            $slider->category_id = isset($request->category_id) ? $request->category_id : 0;
                            $slider->propertys_id = $request->property_id;
                            $slider->save();
                        }
                        $result = Property::with('customer')->with('category:id,category,image')->with('favourite')->with('parameters')->with('interested_users')->where('id', $request->property_id)->get();
                        $property_details = get_property_details($result);
                        $adv->image = "";
                        $adv->save();
                        $userpackage->used_limit_for_advertisement =  $userpackage->used_limit_for_advertisement + 1;
                        $userpackage->save();
                        $response['error'] = false;
                        $response['message'] = "Advertisement add successfully";
                        $response['data'] = $property_details;
                        return response()->json($response);
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Package not found";
                        return response()->json($response);
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Package Limit is over";
                    return response()->json($response);
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function get_advertisement(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $date = date('Y-m-d');
        DB::enableQueryLog();
        $adv = Advertisement::select('id', 'image', 'category_id', 'property_id', 'type', 'customer_id', 'is_enable', 'status')->with('customer:id,name')->where('end_date', '>', $date)->where('is_enable', '1');
        if (isset($request->customer_id)) {
            $adv->where('customer_id', $request->customer_id);
        }

        $total = $adv->get()->count();
        $result = $adv->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();


        if (!$result->isEmpty()) {
            foreach ($adv as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.ADVERTISEMENT_IMAGE_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function get_package(Request $request)
    {
        $date = date('Y-m-d');
        DB::enableQueryLog();
        $package = Package::where('status', 1)->orderBy('id', 'ASC')->where('price', '!=', 0)->get();

        if (!$package->isEmpty()) {

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $package;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function requestPacakge(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        $package = Package::find($request->package_id);

        if(!$customer){
            return response()->json([
                'error'=>true,
                'message'=> 'Custoemr Not Exists'
            ]);
        }

        if(!$package){
            return response()->json([
                'error'=>true,
                'message'=> 'Package Not Exists'
            ]);
        }

        SubscriptionRequest::firstOrCreate([
            'customer_id'=>$request->customer_id,
            'package_id'=>$request->package_id,
            'status'=>'pending'
        ]);

        $response['error'] = false;
        $response['message'] = "Request Sent Successfully!";

        return response()->json($response);
    }

    public function user_purchase_package(Request $request)
    {
        $start_date =  Carbon::now();

        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);

        if (!$validator->fails()) {
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);
            if (isset($request->flag)) {
                $user_exists = UserPurchasedPackage::where('modal_id', $current_user)->get();
                if ($user_exists) {
                    UserPurchasedPackage::where('modal_id', $current_user)->delete();
                }
            }

            $package = Package::find($request->package_id);
            $user = Customer::find($current_user);
            $data_exists = UserPurchasedPackage::where('modal_id', $current_user)->get();
            if (count($data_exists) == 0 && $package) {
                $user_package = new UserPurchasedPackage();
                $user_package->modal()->associate($user);
                $user_package->package_id = $request->package_id;
                $user_package->start_date = $start_date;
                $user_package->end_date = $package->duratio != 0 ? Carbon::now()->addDays($package->duration) : NULL;
                $user_package->save();

                $user->subscription = 1;
                $user->update();

                $response['error'] = false;
                $response['message'] = "purchased package add successfully";
            } else {
                $response['error'] = false;
                $response['message'] = "data already exists or package not found or add flag for add new package";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function get_favourite_property(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;


        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        \DB::enableQueryLog(); // Enable query log


        $favourite = Favourite::where('user_id', $current_user)->select('property_id')->get();
        // dd($favourite);
        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }

        $property_details = Property::whereIn('id', $arr)->with('category:id,category,category_ar,image')->with('parameters')
        ->with('manufacturer:id,manufacturer,manufacturer_ar,image')->with('model:id,model')->with('year:id,year');


        $result = $property_details->orderBy('id', 'ASC')->skip($offset)->take($limit)->get();

        $total = $property_details->count();

        if (!$result->isEmpty()) {
            $result->transform(function ($property) {
                if ($property->propery_type == 0) {
                    $property->propery_type = "Rell";
                } elseif ($property->propery_type == 1) {
                    $property->propery_type = "Rent";
                } elseif ($property->propery_type == 2) {
                    $property->propery_type = "sold";
                } elseif ($property->propery_type == 3) {
                    $property->propery_type = "Rented";
                }
                return $property;
            });
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = get_property_details($result,$current_user);
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    function advertisementRequest(Request $request,$id)
    {
        $property = Property::findOrFail($id);

        Advertisement::firstOrCreate([
            'property_id' => $property->id,
            'customer_id' => $property->added_by,
            'start_date' => Carbon::now(),
            'end_date'=> Carbon::now()->addDays(9),
            'type' => 'Slider',
            'is_enable' => 1,
            'status' => 1,
        ]);

        return response()->json(['message' => 'Advertisement Request Sent Successfully']);
    }

    public function delete_advertisement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if (!$validator->fails()) {
            $adv = Advertisement::find($request->id);
            if (!$adv) {
                $response['error'] = false;
                $response['message'] = "Data not found";
            } else {

                $adv->delete();
                $response['error'] = false;
                $response['message'] = "Advertisement Deleted successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function interested_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required',
            'type' => 'required'
        ]);
        if (!$validator->fails()) {

            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);

            $interested_user = InterestedUser::where('customer_id', $current_user)->where('property_id', $request->property_id);

            if ($request->type == 1) {

                if (count($interested_user->get()) > 0) {
                    $response['error'] = false;
                    $response['message'] = "already added to interested users ";
                } else {
                    $interested_user = new InterestedUser();

                    $interested_user->property_id = $request->property_id;
                    $interested_user->customer_id = $current_user;
                    $interested_user->save();
                    $response['error'] = false;
                    $response['message'] = "Interested Users added successfully";
                    $response['data'] = $interested_user->fresh();
                }
            }
            if ($request->type == 0) {

                if (count($interested_user->get()) == 0) {
                    $response['error'] = false;
                    $response['message'] = "No data found to delete";
                } else {
                    $interested_user->delete();

                    $response['error'] = false;
                    $response['message'] = "Interested Users removed  successfully";
                }
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function delete_inquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',

        ]);

        if (!$validator->fails()) {
            $adv = PropertysInquiry::where('status', 0)->find($request->id);
            if (!$adv) {
                $response['error'] = false;
                $response['message'] = "Data not found";
            } else {

                $adv->delete();
                $response['error'] = false;
                $response['message'] = "Property inquiry Deleted successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function user_interested_property(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 25;
        $payload = JWTAuth::getPayload($this->bearerToken($request));

        $current_user = (string)($payload['customer_id']);
        \DB::enableQueryLog(); // Enable query log

        $favourite = InterestedUser::where('customer_id', $current_user)->select('property_id')->get();

        $arr = array();
        foreach ($favourite as $p) {
            $arr[] =  $p->property_id;
        }

        $property_details = Property::whereIn('id', $arr)->with('category:id,category')->with('parameters');

        $result = $property_details->orderBy('created_at', 'DESC')->skip($offset)->take($limit)->get();

        $total = $result->count();

        if (!$result->isEmpty()) {
            foreach ($property_details as $row) {
                if (filter_var($row->image, FILTER_VALIDATE_URL) === false) {
                    $row->image = ($row->image != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $row->image : '';
                } else {
                    $row->image = $row->image;
                }
            }
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
            $response['total'] = $total;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function get_limits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if (!$validator->fails()) {
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);
            $package = UserPurchasedPackage::where('modal_id', (string)$current_user)->where('package_id', $request->id)->with(['package' => function ($q) {
                $q->select('id', 'property_limit', 'advertisement_limit');
            }])->first();
            //dd($package);
            if (!$package) {
                $response['error'] = true;
                $response['message'] = "package not found";
                return response()->json($response);
            }
            $arr = 0;
            $adv_count = 0;
            $prop_count = 0;

            $adv_count = $package->package->advertisement_limit;
            $prop_count = $package->package->property_limit;

            ($arr = $package->id);

            $advertisement_limit = Advertisement::where('customer_id', $current_user)->where('package_id', $request->id)->get();

            $propeerty_limit = Property::where('added_by', $current_user)->where('package_id', $request->id)->get();


            $response['total_limit_of_advertisement'] = ($adv_count);
            $response['total_limit_of_property'] = ($prop_count);
            $response['used_limit_of_advertisement'] = (int)$package->used_limit_for_advertisement;
            $response['used_limit_of_property'] = (int)$package->used_limit_for_property;
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function get_nearby_properties(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'city' => 'required',

        ]);
        if (!$validator->fails()) {
            $result = Property::select('id', 'price', 'latitude', 'longitude', 'propery_type')->where('city_name', 'LIKE', "%$request->city%")->where('status', 1)->get();
            $rows = array();
            $tempRow = array();
            $count = 1;

            if (!$result->isEmpty()) {

                foreach ($result as $key => $row) {
                    $tempRow['id'] = $row->id;
                    $tempRow['price'] = $row->price;
                    $tempRow['latitude'] = $row->latitude;
                    $tempRow['longitude'] = $row->longitude;
                    if ($row->propery_type == 0) {
                    $tempRow['property_type']= "sell";
                } elseif ($row->propery_type == 1) {
                    $tempRow['property_type']= "rant";
                } elseif ($row->propery_type == 2) {
                    $tempRow['property_type'] = "sold";
                }
                elseif ($row->propery_type == 3) {
                     $tempRow['property_type'] = "Rented";
                }


                    $rows[] = $tempRow;
                    // $parameters[] = $arr;
                    $count++;
                }
            }

            $response['error'] = false;
            $response['data'] = $rows;
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function update_property_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'property_id' => 'required'

        ]);
        if (!$validator->fails()) {
            $property = Property::find($request->property_id);
            $property->status = $request->status;
            $property->save();
            $response['error'] = false;
            $response['message'] = "Data updated Successfully";
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function get_count_by_cities_categoris(Request $request)
    {
        // get count by category

        $categoriesWithCount = Category::withCount('properties')->get();
        $cat_arr = array();
        $city_arr = array();
        $agent_arr = array();


        foreach ($categoriesWithCount as $category) {

            array_push($cat_arr, ['category' => $category->category, 'Count' => $category->properties_count]);
        }

        $response['category_data'] = $cat_arr;

        $cities = City::withCount('properties')
            ->get();

        foreach ($cities as $city) {

            array_push($city_arr, ['City' => $city->city, 'Count' => $city->properties_count, 'image' => $city->image]);

        }
        $response['city_data'] = $city_arr;
        return response()->json($response);
    }

    public function get_agents_details(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        $agent_arr = array();
        $propertiesByAgent = Property::with(['customer' => function ($q) {
            $q->where('role', 1);
        }])
            ->groupBy('added_by')
            ->select('added_by', \DB::raw('count(*) as count'))->skip($offset)->take($limit)
            ->get();

        foreach ($propertiesByAgent as $agent) {
            if (count($agent->customer)) {

                array_push($agent_arr, ['agent' => $agent->added_by, 'Count' => $agent->count, 'customer' => $agent->customer]);
            }
        }
        if (count($agent_arr)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch  Successfully";
            $response['agent_data'] = $agent_arr;
        } else {
            $response['error'] = false;
            $response['message'] = "No Data Found";
        }

        return response()->json($response);
    }

    public function get_report_reasons(Request $request)
    {
        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;

        $report_reason = report_reasons::all();

        if (isset($request->id) && !empty($request->id)) {
            $id = $request->id;
            $report_reason->where('id', '=', $id);
        }
        $result = $report_reason->skip($offset)->take($limit);

        $total = $report_reason->count();

        if (!$result->isEmpty()) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";

            $response['total'] = $total;
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public function add_reports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason_id' => 'required',
            'property_id' => 'required',
        ]);
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        if (!$validator->fails()) {
            $report_count = user_reports::where('property_id', $request->property_id)->where('customer_id', $current_user)->get();
            if (!count($report_count)) {
                $report_reason = new user_reports();
                $report_reason->reason_id = $request->reason_id ? $request->reason_id : 0;
                $report_reason->property_id = $request->property_id;
                $report_reason->customer_id = $current_user;
                $report_reason->other_message = $request->other_message ? $request->other_message : '';



                $report_reason->save();


                $response['error'] = false;
                $response['message'] = "Report Submited Successfully";
            } else {
                $response['error'] = false;
                $response['message'] = "Already Reported";
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function user_interests(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $data = array();
        $current_user = (string)($payload['customer_id']);

        $user_interests = UserInterest::where('user_id', $current_user)->get();
        $total = $user_interests->count();

        $response['error'] = false;
        $response['message'] = "Data Fetched Successfully";

        foreach ($user_interests as $user_interest) {
            array_push($data, [
                'id' => $user_interest->id,
                'user_id' => $user_interest->user_id,
                'manufacturer_ids' => !empty($user_interest->manufacturer_ids) ? Manufacturer::select('id', 'manufacturer','manufacturer_ar', 'image')->where('status', '1')
                                                                                                ->whereIn('id', explode(',', $user_interest->manufacturer_ids))->get() : [],
                'model_ids' => !empty($user_interest->model_ids) ? Modell::select('id', 'model')->where('status', '1')
                                                                                                ->whereIn('id', explode(',', $user_interest->model_ids))->get() : [],
                'city_ids' => !empty($user_interest->city_ids) ? City::select('id', 'city', 'city_ar')->where('status', '1')
                                                                                                ->whereIn('id', explode(',', $user_interest->city_ids))->get() : [],
                'area_ids' => !empty($user_interest->area_ids) ? Area::select('id', 'area', 'area_ar')->where('status', '1')
                                                                                                ->whereIn('id', explode(',', $user_interest->area_ids))->get() : [],
                'price_range' => !empty($user_interest->price_range) ? explode(',', $user_interest->price_range) : [],
                'year_range' => !empty($user_interest->year_range) ? explode(',', $user_interest->year_range) : [],
                'created_at' => $user_interest->created_at,
                ]);
        }
        $response['total'] = $total;
        $response['data'] = $data;
        return response()->json($response);
    }

    public function add_edit_user_interest(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $data = array();
        $current_user = (string)($payload['customer_id']);
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:add,edit',
            'id' => 'required_if:action,=,edit',
            'manufacturer_ids'=> 'required',
            // 'model_ids'=>'required',
            // 'city_ids'=>'required',
            // 'area_ids'=>'required',
            // 'price_range'=>'required',
            // 'year_range'=>'required',
        ]);

        if ($validator->fails()) {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }

        if($request->action == 'edit'){
            $user_interest = UserInterest::find($request->id);

            if(!$user_interest){
                $response['error'] = true;
                $response['message'] = "Not Found!";

                return response()->json($response);
            }
        }
        $user_interest = new UserInterest();
        $user_interest->user_id = $current_user;
        $user_interest->manufacturer_ids = isset($request->manufacturer_ids) ? $request->manufacturer_ids : '';
        $user_interest->model_ids = isset($request->model_ids) ? $request->model_ids : '';
        $user_interest->city_ids = isset($request->city_ids) ? $request->city_ids : '';
        $user_interest->area_ids = isset($request->area_ids) ? $request->area_ids : '';
        $user_interest->price_range = isset($request->price_range) ? $request->price_range : '';
        $user_interest->year_range = isset($request->year_range) ? $request->year_range : '';
        $user_interest->save();

        $data = [
            'id' => $user_interest->id,
            'user_id' => $user_interest->user_id,
            'manufacturer_ids' => !empty($user_interest->manufacturer_ids) ? explode(',', $user_interest->manufacturer_ids) : '',
            'model_ids' => !empty($user_interest->model_ids) ? explode(',', $user_interest->model_ids) : '',
            'city_ids' => !empty($user_interest->city_ids) ?  explode(',', $user_interest->city_ids) : '',
            'area_ids' => !empty($user_interest->area_ids) ? explode(',', $user_interest->area_ids) : '',
            'price_range' => !empty($user_interest->price_range) ? explode(',', $user_interest->price_range) : '',
            'year_range' => !empty($user_interest->year_range) ? explode(',', $user_interest->year_range) : '',
            'created_at' => $user_interest->created_at,
        ];
        $response['error'] = false;
        $response['message'] = "Data Store Successfully";
        $response['data'] = $data;

        return response()->json($response);
    }

    public function delete_interest(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);

        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if (!$validator->fails()) {
            $userInterest = UserInterest::where('user_id', $current_user)->find($request->id);
            if (!$userInterest) {
                $response['error'] = false;
                $response['message'] = "Data not found";
            } else {
                $userInterest->delete();
                $response['error'] = false;
                $response['message'] = "User Interest Deleted successfully";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function get_user_recommendation(Request $request)
    {

        $offset = isset($request->offset) ? $request->offset : 0;
        $limit = isset($request->limit) ? $request->limit : 10;
        try{
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);
        }catch(Exception $e){
            $current_user = $request->current_user;
        }
        DB::enableQueryLog();

        $user_interest = UserInterest::where('user_id', $current_user)->first();

        $property = Property::with('customer')->with('user')->with('category:id,category,category_ar,manufacturer,installment,caysh,image')
        ->with('favourite')->with('parameters')->with('interested_users')
        ->where('status', 1)
        ->whereHas('category', function ($q) {
            $q->where('caysh', 0);
        });

        if ($user_interest) {

            if ($user_interest->manufacturer_ids != '') {
                $manufacturer_ids = explode(',', $user_interest->manufacturer_ids);
                $property = $property->whereIn('manufacturer_id', $manufacturer_ids);
            }

            if ($user_interest->model_ids != '') {

                $model_ids = explode(',', $user_interest->model_ids);
                $property = $property->whereIn('model_id', $model_ids);
            }

            if ($user_interest->city_ids != '') {

                $city_ids = explode(',', $user_interest->city_ids);
                $property = $property->whereIn('city_id', $city_ids);
            }

            // if ($user_interest->area_ids != '') {

            //     $area_ids = explode(',', $user_interest->area_ids);
            //     $property = $property->whereIn('area_id', $area_ids);
            // }

            /*if ($user_interest->year_range != '') {

                $max_year = explode(',', $user_interest->year_range)[1];
                $min_year = explode(',', $user_interest->year_range)[0];

                if (isset($max_year) && isset($min_year)) {

                    $property = $property->where(function ($query) use ($min_year, $max_year) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_year])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_year]);
                    });
                }
            }

            if ($user_interest->price_range != '') {

                $max_price = explode(',', $user_interest->price_range)[1];
                $min_price = explode(',', $user_interest->price_range)[0];

                if (isset($max_price) && isset($min_price)) {
                    $min_price = floatval($min_price);
                    $max_price = floatval($max_price);

                    $property = $property->where(function ($query) use ($min_price, $max_price) {
                        $query->whereRaw("CAST(price AS DECIMAL(10, 2)) >= ?", [$min_price])
                            ->whereRaw("CAST(price AS DECIMAL(10, 2)) <= ?", [$max_price]);
                    });
                }
            }*/

            $total = $property->get()->count();
            $result = $property->skip($offset)->take($limit)->orderBy("created_at", "DESC")->get();

            if (!empty($result)) {
                $property_details = get_property_details($result, $current_user);
                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total'] = $total;
                $response['data'] = $property_details;
            } else {

                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }

        } else {

            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        };
        return ($response);
    }

    public function contct_us(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        if (!$validator->fails()) {
            $contactrequest = new Contactrequests();
            $contactrequest->first_name = $request->first_name;
            $contactrequest->last_name = $request->last_name;
            $contactrequest->email = $request->email;
            $contactrequest->subject = $request->subject;
            $contactrequest->message = $request->message;
            $contactrequest->save();
            $response['error'] = false;
            $response['message'] = "Conatct Request Send successfully";
        } else {
            $response['error'] = true;
            $response['message'] =  $validator->errors()->first();
        }
        return response()->json($response);
    }

    public function get_languages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_code' => 'required',
        ]);

        if (!$validator->fails()) {
            $language = Language::where('code', $request->language_code)->first();

            if ($language) {
                if ($request->web_language_file) {
                    $json_file_path = public_path('web_languages/' . $request->language_code . '.json');
                } else {
                    $json_file_path = public_path('languages/' . $request->language_code . '.json');
                }

                if (file_exists($json_file_path)) {
                    $json_string = file_get_contents($json_file_path);
                    $json_data = json_decode($json_string);

                    if ($json_data !== null) {
                        $language->file_name = $json_data;
                        $response['error'] = false;
                        $response['message'] = "Data Fetch Successfully";
                        $response['data'] = $language;
                    } else {
                        $response['error'] = true;
                        $response['message'] = "Invalid JSON format in the language file";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Language file not found";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Language not found";
            }
        } else {
            $response['error'] = true;
            $response['message'] = $validator->errors()->first();
        }

        return response()->json($response);
    }

    public function get_payment_details(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);

        $payment = Payments::where('customer_id', $current_user);

        $result = $payment->get();

        if (count($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    public $paypal;

    public function paypal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
            'amount' => 'required',
            'user_id'=>'required|exists:customers,id'
        ]);

        $this->paypal = Setting::where('type','LIKE','%paypal%')->get();

        $token = $this->generatePaypalToken();

        if (!$validator->fails()) {
            $current_user = (string)($request['user_id']);
            $paypal = new Paypal();
            $returnURL = url('api/app_payment_status');
            $cancelURL = url('api/app_payment_status');
            $notifyURL = url('webhook/paypal');
            $package_id = $request->package_id;
            // Get product data from the database

            // Get current user ID from the session
            $paypal->add_field('return', $returnURL);
            $paypal->add_field('cancel_return', $cancelURL);
            $paypal->add_field('notify_url', $notifyURL);
            $custom_data = $package_id . ',' . $current_user;

            // Add fields to paypal form
            $paypal->add_field('item_name', "package");
            $paypal->add_field('custom_id', json_encode($custom_data));

            $paypal->add_field('custom', ($custom_data));

            $paypal->add_field('amount', $request->amount);

            // Render paypal form
            $paypal->paypal_auto_form();
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
    }

    function generatePaypalToken(){
        $curl = curl_init();

        if($this->paypal->where('type','paypal_mode')->first()->data == 'test'){
            $url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
        }else{
            $url = 'https://api-m.paypal.com/v1/oauth2/token';
        }

        $user = $this->paypal->where('type','paypal_client_id')->first()->data;
        $password = $this->paypal->where('type','paypal_secret_key')->first()->data;

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '. base64_encode("$user:$password")
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response)->access_token;
    }

    function generatePaypalRedirectUrl($data){

        if($this->paypal->where('type','paypal_mode')->first()->data == 'test'){
            $url = 'https://api-m.sandbox.paypal.com';
        }else{
            $url = 'https://api-m.paypal.com';
        }

        $refId = $data['ref_id'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url . '/v2/checkout/orders',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "intent": "CAPTURE",
        "purchase_units": [
            {
            "reference_id": "'.$refId.'",
            "amount": {
                "currency_code": "USD",
                "value": "100.00"
            }
            }
        ],
        "payment_source": {
            "paypal": {
            "experience_context": {
                "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
                "brand_name": "EXAMPLE INC",
                "locale": "en-US",
                "landing_page": "LOGIN",
                "shipping_preference": "NO_SHIPPING",
                "user_action": "PAY_NOW",
                "return_url": "https://example.com/returnUrl",
                "cancel_url": "https://example.com/cancelUrl"
            }
            }
        }
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'PayPal-Request-Id: 7b92603e-77ed-4896-8e78-5dea2050476a',
            'Authorization: Bearer A21AAIM-967h1FrGaWHToaabTTFp4naiNTLja7z-YoxYxRKPk0LVfaPl_Dmhh2i2gyBKXDX9SAEshKLPafyKj9c4iz8i0nw2A'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
    }

    public function app_payment_status(Request $request)
    {
        $paypalInfo = $request->all();

        if (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "completed") {

            $response['error'] = false;
            $response['message'] = "Your Purchase Package Activate Within 10 Minutes ";
            $response['data'] = $paypalInfo['txn_id'];
        } elseif (!empty($paypalInfo) && isset($_GET['st']) && strtolower($_GET['st']) == "authorized") {

            $response['error'] = false;
            $response['message'] = "Your payment has been Authorized successfully. We will capture your transaction within 30 minutes, once we process your order. After successful capture Ads wil be credited automatically.";
            $response['data'] = $paypalInfo;
        } else {
            $response['error'] = true;
            $response['message'] = "Payment Cancelled / Declined ";
            $response['data'] = (isset($_GET)) ? $paypalInfo : "";
        }
        return (response()->json($response));
    }

    public function get_payment_settings(Request $request)
    {
        $payment_settings =
            Setting::select('type', 'data')->whereIn('type', ['paypal_business_id', 'sandbox_mode', 'paypal_gateway', 'razor_key', 'razor_secret', 'razorpay_gateway', 'paystack_public_key', 'paystack_secret_key', 'paystack_currency', 'paystack_gateway', 'stripe_publishable_key', 'stripe_currency', 'stripe_gateway', 'stripe_secret_key']);

        $result = $payment_settings->get();

        if (count($result)) {
            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $result;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return (response()->json($response));
    }

    public function send_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'message' => 'required',
            'property_id' => 'required',
        ]);
        $fcm_id = array();
        if (!$validator->fails()) {

            $chat = new Chats();
            $chat->sender_id = (string)$request->sender_id;
            $chat->receiver_id = (string)$request->receiver_id;
            $chat->property_id = $request->property_id;
            $chat->message = $request->message;
            $destinationPath = public_path('images') . config('global.CHAT_FILE');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // image upload
            if ($request->hasFile('file')) {
                // dd('in');
                $file = $request->file('file');
                $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $chat->file = $fileName;
            } else {
                $chat->file = '';
            }

            $audiodestinationPath = public_path('images') . config('global.CHAT_AUDIO');
            if (!is_dir($audiodestinationPath)) {
                mkdir($audiodestinationPath, 0777, true);
            }
            if ($request->hasFile('audio')) {
                // dd('in');
                $file = $request->file('audio');
                $fileName = microtime(true) . "." . $file->getClientOriginalExtension();
                $file->move($audiodestinationPath, $fileName);
                $chat->audio = $fileName;
            } else {
                $chat->audio = '';
            }
            $chat->save();
            $customer = Customer::select('id', 'fcm_id', 'name','profile')->with(['usertokens' => function ($q) {
                $q->select('fcm_id', 'id', 'customer_id');
            }])->find($request->receiver_id);
            $property = Property::find($request->property_id);
            // dd($customer->toArray());
            if ($customer) {

                foreach ($customer->usertokens as $usertokens) {

                    array_push($fcm_id, $usertokens->fcm_id);
                }
                // $fcm_id = [$customer->usertokens->fcm_id];

                $username = $customer->name;
                $profile = $customer->profile;
            }
            $user_data = User::select('fcm_id', 'name')->get();

            if (!$customer && $property->added_by == 0) {
                $username = "Admin";
                $profile = "";

                foreach ($user_data as $user) {
                    array_push($fcm_id, $user->fcm_id);
                }
            };
            $customer = Customer::select('fcm_id', 'name')->find($request->sender_id);

            $Property = Property::find($request->property_id);
            $fcmMsg = array(
                'title' => 'Message',
                'message' => $request->message,
                'type' => 'chat',
                'body' => $request->message,
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'file' => $chat->file,
                'username' => $username,
                'user_profile' => $profile,
                'audio' => $chat->audio,
                'date' => $chat->created_at,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'default',
                'time_ago' => $chat->created_at->diffForHumans(now(), CarbonInterface::DIFF_RELATIVE_AUTO, true),
                'property_id' => $Property->id,
                'property_title_image' => $Property->title_image,
                'title' => $Property->title,
            );

            $send = send_push_notification($fcm_id, $fcmMsg);

            $response['error'] = false;
            $response['message'] = "Data Store Successfully";
            $response['id'] = $chat->id;
            $response['data'] = $send;
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return (response()->json($response));
    }

    public function get_messages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'property_id' => 'required'

        ]);
        if (!$validator->fails()) {
            $payload = JWTAuth::getPayload($this->bearerToken($request));
            $current_user = (string)($payload['customer_id']);
            // dd($current_user);

            $tempRow = array();
            $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
            $page = $request->page ?? 1; // Get the current page from the query string, or default to 1
            $chat = Chats::where('property_id', $request->property_id)
                ->where(function ($query) use ($request) {
                    $query->where('sender_id', $request->user_id)
                        ->orWhere('receiver_id', $request->user_id);
                })
                ->Where(function ($query) use ($current_user) {
                    $query->where('sender_id', $current_user)
                        ->orWhere('receiver_id', $current_user);
                })
                ->orderBy('created_at', 'DESC')
                //  ->get();
                ->paginate($perPage, ['*'], 'page', $page);

            // You can then pass the $chat object to your view to display the paginated results.



            // dd($chat->toArray());
            if ($chat) {

                $response['error'] = false;
                $response['message'] = "Data Fetch Successfully";
                $response['total_page'] = $chat->lastPage();
                $response['data'] = $chat;
            } else {
                $response['error'] = false;
                $response['message'] = "No data found!";
                $response['data'] = [];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Please fill all data and Submit";
        }
        return response()->json($response);
    }

    public function get_chats(Request $request)
    {
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        $perPage = $request->per_page ? $request->per_page : 15; // Number of results to display per page
        $page = $request->page ?? 1;

        $chat = Chats::with(['sender', 'receiver'])->with('property')
            ->select('id', 'sender_id', 'receiver_id', 'property_id', 'created_at')
            ->where('sender_id', $current_user)
            ->orWhere('receiver_id', $current_user)
            ->orderBy('id', 'desc')
            ->groupBy('property_id')
            ->paginate($perPage, ['*'], 'page', $page);

        if (!$chat->isEmpty()) {

            $rows = array();

            $count = 1;

            $response['total_page'] = $chat->lastPage();

            foreach ($chat as $key => $row) {
                $tempRow = array();
                $tempRow['property_id'] = $row->property_id;
                $tempRow['title'] = $row->property->title;
                $tempRow['title_image'] = $row->property->title_image;
                $tempRow['date'] = $row->created_at;
                $tempRow['property_id'] = $row->property_id;
                if (!$row->receiver || !$row->sender) {
                    $user =
                        user::where('id', $row->sender_id)->orWhere('id', $row->receiver_id)->select('id')->first();

                    $tempRow['user_id'] = 0;
                    $tempRow['name'] = "Admin";
                    $tempRow['profile'] = '';

                    // $tempRow['fcm_id'] = $row->receiver->fcm_id;
                } else {
                    if ($row->sender->id == $current_user) {

                        $tempRow['user_id'] = $row->receiver->id;
                        $tempRow['name'] = $row->receiver->name;
                        $tempRow['profile'] = $row->receiver->profile;
                        $tempRow['firebase_id'] = $row->receiver->firebase_id;
                        $tempRow['fcm_id'] = $row->receiver->fcm_id;
                    }
                    if ($row->receiver->id == $current_user) {
                        $tempRow['user_id'] = $row->sender->id;
                        $tempRow['name'] = $row->sender->name;

                        $tempRow['profile'] = $row->sender->profile;
                        $tempRow['firebase_id'] = $row->sender->firebase_id;
                        $tempRow['fcm_id'] = $row->sender->fcm_id;
                    }
                }
                $rows[] = $tempRow;
                // $parameters[] = $arr;
                $count++;
            }

            $response['error'] = false;
            $response['message'] = "Data Fetch Successfully";
            $response['data'] = $rows;
        } else {
            $response['error'] = false;
            $response['message'] = "No data found!";
            $response['data'] = [];
        }
        return response()->json($response);
    }

    function generatePaymentUrl(Request $request) {

        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $payload = JWTAuth::getPayload($this->bearerToken($request));

        $metaDataSuccess = $this->stripeMetadata([
            'customer_id'=>$payload['customer_id'],
            'package_id'=>$request->package_id,
            'status'=>'success'
        ]);

        $metaDataCancel = $this->stripeMetadata([
            'customer_id'=>$payload['customer_id'],
            'package_id'=>$request->package_id,
            'status'=>'cancel'
        ]);

        $stripe_currency = system_setting('stripe_currency');
        $package = Package::find($request->package_id);

        $stripe = new \Stripe\StripeClient(system_setting('stripe_secret_key'));

        $checkout = $stripe->checkout->sessions->create([
            'success_url' => url('api/stripe/status?d='.$metaDataSuccess),
            'cancel_url'  => url('api/stripe/status?d='.$metaDataCancel),
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $stripe_currency,
                        'unit_amount' => $package['price']*100,
                        'product_data' => ['name' => $package->name],
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
        ]);

        Metadata::create([
            'key'=>$this->stripeMetadata([
                'customer_id'=>$payload['customer_id'],
                'package_id'=>$request->package_id
            ]),
            'value'=>$checkout
        ]);

        return $checkout->url;
    }

    function stripeStatus(Request $request){
        $data = json_decode(base64_decode($request->d), true);

        $key = $this->stripeMetadata([
            'customer_id'=>$data['customer_id'],
            'package_id'=>$data['package_id']
        ]);

        $metaData = Metadata::where('key',$key)->first();

        if($data['status'] == 'success'){
            $user_id = $data['customer_id'];
            $package_id = $data['package_id'];

            $payment = Payments::whereTransactionId($metaData->value['id'])->first();
            if(!$payment){
                $payment = new Payments();
            }
            $payment->transaction_id = $metaData->value['id'];
            $payment->amount = $metaData->value['amount_total']/100;
            $payment->package_id = $package_id;
            $payment->customer_id = $user_id;
            $payment->status = 1;
            $payment->payment_gateway = "stripe";
            $payment->save();
            $start_date =  Carbon::now();

            $user = Customer::find($user_id);
            $package = Package::find($package_id);
            $data_exists = UserPurchasedPackage::where('modal_id', $user_id)->get();
            if ($data_exists) {
                UserPurchasedPackage::where('modal_id', $user_id)->delete();
            }
            if ($package) {
                $user_package = new UserPurchasedPackage();
                $user_package->modal()->associate($user);
                $user_package->package_id = $package_id;
                $user_package->start_date = $start_date;
                $user_package->end_date = $package->duration != 0 ? Carbon::now()->addDays($package->duration) : NULL;
                $user_package->save();
                // if ($data_exists) {
                //       UserPurchasedPackage::where('modal_id', $user_id)->where('package_id','!=',$user_package->id)->delete();
                //      }
                $user->subscription = 1;
                $user->save();
            }

            // $user->package_id = $data['package_id'];
            // $user->package_end_date = Carbon::now()->addDays($package->days);


            return redirect()->to('https://3jlcom.com/payment-success');
        }else{
            return redirect()->to('https://3jlcom.com/payment-error');
        }
    }

    function stripeMetadata($data) {
        return base64_encode(json_encode($data));
    }

    public function delete_property(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ], 400);
        }
        $property = Property::find($request->id);
        if ($property) {
            if ($property->delete()) {

                $chat = Chats::where('property_id', $property->id);
                if ($chat) {
                    $chat->delete();
                }

                $enquiry = PropertysInquiry::where('propertys_id', $property->id);
                if ($enquiry) {
                    $enquiry->delete();
                }

                $slider = Slider::where('propertys_id', $property->id);
                if ($slider) {
                    $slider->delete();
                }


                $notifications = Notifications::where('propertys_id', $property->id);
                if ($notifications) {
                    $notifications->delete();
                }

                if ($property->title_image != '') {
                    if (file_exists(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image)) {
                        unlink(public_path('images') . config('global.PROPERTY_TITLE_IMG_PATH') . $property->title_image);
                    }
                }
                foreach ($property->gallery as $row) {
                    if (PropertyImages::where('id', $row->id)->delete()) {
                        if ($row->image_url != '') {
                            if (file_exists(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image)) {
                                unlink(public_path('images') . config('global.PROPERTY_GALLERY_IMG_PATH') . $property->id . "/" . $row->image);
                            }
                        }
                    }
                }




                $slider = Slider::where('propertys_id', $property->id)->get();

                foreach ($slider as $row) {
                    $image = $row->image;

                    if (Slider::where('id', $row->id)->delete()) {
                        if (file_exists(public_path('images') . config('global.SLIDER_IMG_PATH') . $image)) {
                            unlink(public_path('images') . config('global.SLIDER_IMG_PATH') . $image);
                        }
                    }
                }

                $response['error'] = false;
                $response['message'] =  'Delete Successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'something wrong';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not found';
        }
        return response()->json($response);
    }

    public function assign_free_package(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'package_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
            ], 400);
        }
        $payload = JWTAuth::getPayload($this->bearerToken($request));
        $current_user = (string)($payload['customer_id']);
        $user = Customer::find($current_user);
        $start_date =  Carbon::now();

        $package = Package::where('price', 0)->find($request->package_id);
        $data_exists = UserPurchasedPackage::where('modal_id', $current_user)->get();

        if ($package) {

            $user_package = new UserPurchasedPackage();
            $user_package->modal()->associate($user);
            $user_package->package_id = $request->package_id;
            $user_package->start_date = $start_date;
            $user_package->end_date = $package->duration != 0 ? Carbon::now()->addDays($package->duration) : NULL;
            $user_package->save();
            if ($data_exists) {
                UserPurchasedPackage::where('modal_id', $current_user)->where('id', '!=', $user_package->id)->delete();
            }
            $user->subscription = 1;
            $user->update();
            $response['error'] = false;
            $response['message'] =  'Package Purchased Successfully';
        } else {
            $response['error'] = false;
            $response['message'] =  'Package Not Found';
        }
        return response()->json($response);
    }
}
