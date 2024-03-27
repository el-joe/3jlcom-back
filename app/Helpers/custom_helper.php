<?php

use App\Models\Advertisement;
use App\Models\Customer;
use App\Models\Favourite;
use App\Models\InterestedUser;
use App\Models\Language;
use App\Models\parameter;
use App\Models\Property;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use App\Models\UserPurchasedPackage;
use App\Models\Usertokens;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use kornrunner\Blurhash\Blurhash;
use Intervention\Image\ImageManagerStatic as Image;


if (!function_exists('system_setting'))
{

    function system_setting($type)
    {

        $db = Setting::where('type', $type)->first();
        return (isset($db)) ? $db->data : '';
    }
}

function generate_dynamic_link ($longUrl) {
    $key = 'AIzaSyAVbVr22_RrSflA83ASIdf6QBasd4l5HMs';
    $url = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=' . $key;
    $data = array(
     "dynamicLinkInfo" => array(
        "dynamicLinkDomain" => "3jlcom.page.link",
        "link" => $longUrl
     )
    );

    $headers = array('Content-Type: application/json');

    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_POST, true );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($data) );

    $data = curl_exec ( $ch );
    curl_close ( $ch );

    $short_url = json_decode($data);
    if(isset($short_url->error)){
      return $short_url->error->message;
    } else {
      return $short_url->shortLink;
    }
}

function form_submit($data = '', $value = '', $extra = '')
{
    $defaults = array(
        'type' => 'submit',
        'name' => is_array($data) ? '' : $data,
        'value' => $value
    );

    return '<input ' . _parse_form_attributes($data, $defaults) . _attributes_to_string($extra) . " />\n";
}

function _parse_form_attributes($attributes, $default)
{
    if (is_array($attributes)) {
        foreach ($default as $key => $val) {
            if (isset($attributes[$key])) {
                $default[$key] = $attributes[$key];
                unset($attributes[$key]);
            }
        }

        if (count($attributes) > 0) {
            $default = array_merge($default, $attributes);
        }
    }

    $att = '';

    foreach ($default as $key => $val) {
        if ($key === 'value') {
            $val = ($val);
        } elseif ($key === 'name' && !strlen($default['name'])) {
            continue;
        }

        $att .= $key . '="' . $val . '" ';
    }

    return $att;
}

if (!function_exists('_attributes_to_string')) {
    /**
     * Attributes To String
     *
     * Helper function used by some of the form helpers
     *
     * @param	mixed
     * @return	string
     */
    function _attributes_to_string($attributes)
    {
        if (empty($attributes)) {
            return '';
        }

        if (is_object($attributes)) {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes)) {
            $atts = '';

            foreach ($attributes as $key => $val) {
                $atts .= ' ' . $key . '="' . $val . '"';
            }

            return $atts;
        }

        if (is_string($attributes)) {
            return ' ' . $attributes;
        }

        return FALSE;
    }
}

if (!function_exists('send_push_notification')) {
    //send Notification
    function send_push_notification($registrationIDs = array(), $fcmMsg = '')
    {
        $get_fcm_key = DB::table('settings')->select('data')->where('type', 'fcm_key')->first();
        $fcm_key = $get_fcm_key->data;

        $registrationIDs_chunks = array_chunk($registrationIDs, 1000);
        // dd($registrationIDs);
        $unregisteredIDs = array(); // Array to store unregistered FCM IDs

        foreach ($registrationIDs_chunks as $registrationIDsChunk) {
            // dd("in");
            $fcmFields = array(
                'registration_ids' => $registrationIDsChunk, // expects an array of ids
                'priority' => 'high',
                'notification' => $fcmMsg,
                'data' => $fcmMsg
            );

            $headers = array(
                'Authorization: key=' . $fcm_key,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
            $get_result = curl_exec($ch);

            curl_close($ch);
            $result = json_decode($get_result, true);
            //  dd($result);
            // Check for unregistered FCM IDs in the response
            if (isset($result['results'])) {
                foreach ($result['results'] as $index => $response) {
                    // dd($response);
                    if (isset($response['error']) && $response['error'] == 'NotRegistered') {
                        // dd("in");
                        $unregisteredIDs[] = $registrationIDsChunk[$index];
                    }
                }
            }
        }

        if (count($unregisteredIDs)) {

            $users = Usertokens::whereIn('fcm_id', $unregisteredIDs)->delete();
        }

        if(isset($result)){

            return $result;
        }
        return false;
    }
}

if (!function_exists('get_countries_from_json')) {
    function get_countries_from_json()
    {
        $country =  json_decode(file_get_contents(public_path('json') . "/cities.json"), true);

        $tempRow = array();
        foreach ($country['countries'] as $row) {
            $tempRow[] = $row['country'];
        }
        return $tempRow;
    }
}

if (!function_exists('get_states_from_json')) {
    function get_states_from_json($country)
    {


        $state =  json_decode(file_get_contents(public_path('json') . "/cities.json"), true);

        $tempRow = array();
        foreach ($state['countries'] as $row) {
            // echo $row;
            if ($row['country'] == $country) {
                $tempRow = $row['states'];
            }
        }

        return $tempRow;
    }
}

if (!function_exists('parameterTypesByCategory')) {
    function parameterTypesByCategory($category_id)
    {


        $parameter_types = DB::table('categories')->select('parameter_types')->where('categories.id', $category_id)->first();

        $tempRow = array();

        $parameterTypes = explode(',', $parameter_types->parameter_types);

        foreach ($parameterTypes as $key => $row) {
            $par_name = parameter::find($row);
            $tempRow['parameters'][$key] = $par_name;

            // $tempRow['parameters'][$key]['type'] = $par_name->type_of_parameter;
        }
        return  $tempRow;
    }
}

function update_subscription()
{
    $data = UserPurchasedPackage::where('user_id', Auth::id())->where('end_date', Carbon::now());
    if ($data) {
        $Customer = Customer::find(Auth::id());
        $Customer->subscription = 0;
        $Customer->update();
    }
}

function get_hash($img)
{

    $image_make = Image::make($img);
    $width = $image_make->width();
    $height = $image_make->height();

    $pixels = [];
    for ($y = 0; $y < $height; ++$y) {
        $row = [];
        for ($x = 0; $x < $width; ++$x) {
            $colors = $image_make->pickColor($x, $y);

            $row[] = [$colors[0], $colors[1], $colors[2]];
        }
        $pixels[] = $row;
    }

    $components_x = 4;
    $components_y = 3;
    $hash =  Blurhash::encode($pixels, $components_x, $components_y);
    //  "ll";
    return $hash;
}

if (!function_exists('form_hidden')) {
    /**
     * Hidden Input Field
     *
     * Generates hidden fields. You can pass a simple key/value string or
     * an associative array with multiple values.
     *
     * @param	mixed	$name		Field name
     * @param	string	$value		Field value
     * @param	bool	$recursing
     * @return	string
     */
    function form_hidden($name, $value = '', $recursing = FALSE)
    {
        static $form;

        if ($recursing === FALSE) {
            $form = "\n";
        }

        if (is_array($name)) {
            foreach ($name as $key => $val) {
                form_hidden($key, $val, TRUE);
            }

            return $form;
        }

        if (!is_array($value)) {
            $form .= '<input type="hidden" name="' . $name . '" value="' . ($value) . "\" />\n";
        } else {
            foreach ($value as $k => $v) {
                $k = is_int($k) ? '' : $k;
                form_hidden($name . '[' . $k . ']', $v, TRUE);
            }
        }

        return $form;
    }
}

if (!function_exists('form_close')) {
    /**
     * Form Close Tag
     *
     * @param	string
     * @return	string
     */
    function form_close($extra = '')
    {
        return '</form>' . $extra;
    }
}

function get_property_details($result, $current_user = NULL)
{
    $rows = array();
    $tempRow = array();
    $adminData = array();
    $count = 1;
    $rows1 = array();
    $tempRow1 = array();
    $count1 = 1;

    foreach ($result as $row) {

        $customer = $row->customer->first();
        $user = $row->user->first();

        $tempRow['id'] = $row->id;
        $tempRow['title'] = $row->title;
        $tempRow['price'] = $row->price;
        $tempRow['installment'] = $row->installment;
        $tempRow['installment_price'] = $row->installment_price;
        $tempRow['installment_down'] = $row->installment_down;
        $tempRow['installment_type'] = $row->installment_type;

        if ($customer && $row->added_by != 0) {

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

            $tempRow['customer'] = $customerData;

        } else if (!$customer || $row->added_by == 0) {

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
            $tempRow['customer']= $adminData;
        }

        $tempRow['category'] = $row->category;
        $tempRow['manufacturer'] = $row->manufacturer;
        $tempRow['model'] = $row->model;
        $tempRow['year'] = $row->year;
        $tempRow['description'] = $row->description;
        $tempRow['address'] = $row->address;
        $tempRow['client_address'] = $row->client_address;

        if ($row->status == 0) {
            $tempRow['propery_type']= "sold";
        } elseif ($row->propery_type == 1) {
            $tempRow['propery_type']= "rant";
        } elseif ($row->status == 1) {
            $tempRow['propery_type'] = "sell";
        }
        elseif ($row->propery_type == 3) {
             $tempRow['propery_type'] = "Rented";
        }

        $tempRow['title_image'] = $row->title_image;
        $tempRow['title_image_hash'] = $row->title_image_hash != '' ? $row->title_image_hash : '';
        $tempRow['gallery'] = $row->gallery;
        $tempRow['threeD_image'] = $row->threeD_image;

        $tempRow['post_created'] = $row->created_at->diffForHumans();
        Carbon::setLocale('ar');
        $tempRow['post_created_ar'] = $row->created_at->diffForHumans();

        $tempRow['total_view'] = $row->total_click;
        $tempRow['status'] = $row->status;
        $tempRow['area'] = $row->area;
        $tempRow['city'] = $row->city;
        $tempRow['city_name'] = $row->city_name;
        $tempRow['state'] = $row->state;
        $tempRow['country'] = $row->country;
        $tempRow['latitude'] = $row->latitude;
        $tempRow['longitude'] = $row->longitude;
        $tempRow['added_by'] = (int)$row->added_by;
        $tempRow['video_link'] = $row->video_link;
        $tempRow['dynamic_link'] = $row->dynamic_link;

        $inquiry = PropertysInquiry::where('customers_id', $current_user)->where('propertys_id', $row->id)->first();

        if ($inquiry) {
            $tempRow['inquiry'] = true;
        } else {
            $tempRow['inquiry'] = false;
        }
        $promoted = Advertisement::where('property_id', $row->id)->where('is_enable', 1)->where('status', 0)->first();

        if ($promoted) {
            $tempRow['promoted'] = true;
        } else {
            $tempRow['promoted'] = false;
        }
        $interested_users = array();
        $favourite_users = array();
        $s = '';
        foreach ($row->favourite as $favourite_user) {

            if ($favourite_user->property_id == $row->id) {

                array_push($favourite_users, $favourite_user->user_id);
                $s .= $favourite_user->user_id . ',';
            }
        }

        foreach ($row->interested_users as $interested_user) {

            if ($interested_user->property_id == $row->id) {

                array_push($interested_users, $interested_user->customer_id);
                $s .= $interested_user->user_id . ',';
            }
        }

        $inquired_users = PropertysInquiry::where('propertys_id', $row->id)->get();

        $tempRow['inquired_users'] = [];

        if (!$inquired_users->isEmpty()) {
            foreach ($inquired_users as $inquired_user) {
                $customer_data = Customer::where('id',$inquired_user->customers_id)->first();
                $inquire_user = [
                    'id' => $inquired_user->id,
                    'property_id' => (int)$inquired_user->propertys_id,
                    'customer_id' => (int)$inquired_user->customers_id,
                    'customer_name' => $customer_data->name,
                    'customer_mobile' => $customer_data->mobile,
                    'offer' => $inquired_user->offer,
                    'status' => $inquired_user->status,
                    'created_at' => $inquired_user->created_at,
                ];
                array_push($tempRow['inquired_users'], $inquire_user);
            }
        }

        $favourite = Favourite::where('property_id', $row->id)->where('user_id', $current_user)->get();

        $interest = InterestedUser::where('property_id', $row->id)->where('customer_id', $current_user)->get();


        if (count($favourite) != 0) {
            $tempRow['is_favourite'] = 1;
        } else {
            $tempRow['is_favourite'] = 0;
        }

        if (count($interest) != 0) {
            $tempRow['is_interested'] = 1;
        } else {
            $tempRow['is_interested'] = 0;
        }
        $tempRow['favourite_users'] = $favourite_users;
        $tempRow['interested_users'] = $interested_users;

        $tempRow['total_inquired_users'] = count($inquired_users);
        $tempRow['total_interested_users'] = count($interested_users);
        $tempRow['total_favourite_users'] = count($favourite_users);


        $arr = [];
        $arr1 = [];

        if ($row->advertisement) {
            $tempRow['advertisement'] = $row->advertisement;
        }

        $tempRow['parameters'] = [];

        foreach ($row->parameters as $res) {

            if ((is_string($res->pivot->value) && is_array(json_decode($res->pivot->value, true)))||
                (is_string($res->pivot->value_ar) && is_array(json_decode($res->pivot->value_ar, true)))) {
                $value = json_decode($res->pivot->value, true);
                $valueAr = json_decode($res->pivot->value_ar, true);
            } else {
                if ($res->type_of_parameter == "file") {
                     if($res->pivot->value=="null"||$res->pivot->value_ar=="null"){
                         $value = "";
                         $valueAr = "";
                    }else{
                         $value = url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' .  $res->pivot->value;
                         $valueAr = url('') . config('global.IMG_PATH') . config('global.PARAMETER_IMG_PATH') . '/' .  $res->pivot->valueAr;
                    }


                } else {
                    if($res->pivot->value=="null"||$res->pivot->value_ar=="null"){
                         $value = "";
                         $valueAr = "";
                    }else{
                         $value = $res->pivot->value;
                         $valueAr = $res->pivot->value_ar;
                    }

                }
            }

            $parameter = [
                'id' => $res->id,
                'name' => $res->name,
                'name_ar' => $res->name_ar,
                'type_of_parameter' => $res->type_of_parameter,
                'type_values' => $res->type_values,
                'type_values_ar' => stripslashes($res->type_values_ar),
                'type_values_arr' => explode(',',$res->type_values_ar),
                'image' => $res->image,
                'value' => $value,
                'value_ar' => $valueAr,
            ];
            array_push($tempRow['parameters'], $parameter);
        }


        $rows[] = $tempRow;
        $parameters[] = $arr;
        $count++;
    }
    return $rows;
}

function get_language()
{
    return Language::get();
}

function get_unregistered_fcm_ids($registeredIDs = array())
{

    // Convert the arrays to lowercase for case-insensitive comparison
    $registeredIDsLower = array_map('strtolower', $registeredIDs);



    // Retrieve the FCM IDs from the 'usertoken' table
    $fcmIDs = Usertokens::pluck('fcm_id')->toArray();

    // Now you have an array ($fcmIDs) containing all the FCM IDs from the 'usertoken' table

    $allIDsLower = array_map('strtolower', $fcmIDs);


    // Use array_diff to find the FCM IDs that are not registered
    $unregisteredIDsLower = array_diff($allIDsLower, $registeredIDsLower);
    // dd($unregisteredIDsLower);


    // Convert the IDs back to their original case
    $unregisteredIDs = array_map('strtoupper', $unregisteredIDsLower);
    Usertokens::WhereIn('fcm_id', $fcmIDs)->delete();
}
