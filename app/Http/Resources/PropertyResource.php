<?php

namespace App\Http\Resources;

use App\Models\Advertisement;
use App\Models\Customer;
use App\Models\Favourite;
use App\Models\InterestedUser;
use App\Models\Property;
use App\Models\PropertysInquiry;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        try{
            $payload = JWTAuth::getPayload($request->bearerToken());
            $current_user = (string)($payload['customer_id']);
            if(!$current_user){
                $current_user = request('current_user')??'';
            }
        }catch(Exception $e){
            $current_user = request('current_user')??'';
        }

        $ads = $this->advertisement;
        $isSpecial = $ads->where('is_enable', 1)->where('status', 0)->count() > 0 ? true : false;
        $specialRequested = $ads->where('status','!=', 0)->count() > 0 ? true : false;

        $customer = $this->customer->first();

        if($customer || $this->added_by != 0){
            $customerResource = new CustomerResource($customer);
        }else{
            $mobile = Setting::where('type', 'company_tel1')->pluck('data');
            $email = Setting::where('type', 'company_email')->pluck('data');
            $address = Setting::where('type', 'company_address')->pluck('data');
            $logo = Setting::where('type', 'favicon_icon')->pluck('data');
            $about = Setting::where('type', 'company_address')->pluck('data');

            $customerResource['id'] = 0;
            $customerResource['name'] = "3jlcom - عجلكم";
            $customerResource['email'] = $email[0];
            $customerResource['mobile'] = $mobile[0];
            $customerResource['profile'] = url('') . config('global.LOGO_PATH') . $logo[0];
            $customerResource['address'] = $address[0];
            $customerResource['role'] = 1;
            $customerResource['isActive'] = true;
            $customerResource['isVerified'] = true;
            $customerResource['about'] = $about[0];
            $customerResource['instagram_link'] = $address[0];
            $customerResource['twitter_link'] = $address[0];
            $customerResource['facebook_link'] = $address[0];
            $customerResource['customertotalpost'] = Property::where('added_by', $this->added_by)->count();
        }

        $propertyType = "";
        if ($this->status == 0) {
            $propertyType = "sold";
        } elseif ($this->propery_type == 1) {
            $propertyType = "rant";
        } elseif ($this->status == 1) {
            $propertyType = "sell";
        } elseif ($this->propery_type == 3) {
            $propertyType = "Rented";
        }

        $enCreated = $this->created_at->diffForHumans();
        Carbon::setLocale('ar');
        $arCreated = $this->created_at->diffForHumans();

        $interestedUsers = $this->interested_users->filter(fn($q)=>$q->property_id==$this->id)->pluck('customer_id')->toArray();
        $favoriteUsers = $this->favourite->filter(fn($q)=>$q->property_id==$this->id)->pluck('user_id')->toArray();


        $inquiredUsers = PropertysInquiry::where('propertys_id', $this->id)->get()
        ->map(function ($inquired_user){
            $customer_data = Customer::where('id',$inquired_user->customers_id)->first();
            return  [
                'id' => $inquired_user->id,
                'property_id' => (int)$inquired_user->propertys_id,
                'customer_id' => (int)$inquired_user->customers_id,
                'customer_name' => $customer_data->name,
                'customer_mobile' => $customer_data->mobile,
                'customer'=>$customer_data,
                'offer' => $inquired_user->offer,
                'status' => $inquired_user->status,
                'created_at' => $inquired_user->created_at,
            ];
        });

        $data = [
            'id'=>$this->id,
            'title'=>$this->title,
            'price'=>$this->price,
            'installment'=>$this->installment,
            'installment_price'=>$this->installment_price,
            'installment_down'=>$this->installment_down,
            'installment_type'=>$this->installment_type,
            'is_special'=> $isSpecial,
            'special_requested'=> $specialRequested,
            'customer'=> $customerResource,
            'category'=>$this->category,
            'manufacturer'=>$this->manufacturer,
            'model'=>$this->model,
            'year'=>$this->year,
            'description'=>$this->description,
            'address'=>$this->address,
            'client_address'=>$this->client_address,
            'propery_type' => $propertyType,
            'title_image'=>$this->title_image,
            'title_image_hash'=> $this->title_image_hash ?? '',
            'gallery'=>$this->gallery,
            'threeD_image'=>$this->threeD_image,
            'post_created'=> $enCreated,
            'post_created_ar'=> $arCreated,
            'total_view'=> $this->total_click,
            'status'=>$this->status,
            'area'=>$this->area,
            'city'=>$this->city,
            'city_name'=>$this->city_name,
            'state'=>$this->state,
            'country'=>$this->country,
            'latitude'=>$this->latitude,
            'longitude'=>$this->longitude,
            'added_by'=>(int)$this->added_by,
            'video_link'=>$this->video_link,
            'dynamic_link'=>$this->dynamic_link,
            'inquiry'=> PropertysInquiry::where('customers_id', $current_user)
                ->where('propertys_id', $this->id)->count() > 0,
            'promoted'=> Advertisement::where('property_id', $this->id)->where('is_enable', 1)->where('status', 0)->count() > 0,
            'interested_users'=>$interestedUsers,
            'favorite_users'=> $favoriteUsers,
            'inquired_users'=>$inquiredUsers,
            'is_favorite'=>Favourite::where('property_id', $this->id)
                ->where('user_id', $current_user)->count() > 0 ? 1 : 0,
            'is_interested'=>InterestedUser::where('property_id', $this->id)
                ->where('customer_id', $current_user)->count() > 0 ? 1 : 0,
            'total_inquired_users'=> $inquiredUsers->count(),
            'total_interested_users'=> $inquiredUsers->count(),
            'total_favourite_users'=> $inquiredUsers->count(),
            'parameters'=> $this->parameters->map(function ($res) {
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

                return [
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
            })
        ];

        if($this->advertisement){
            $data['advertisement'] = $this->advertisement;
        }

        return $data;
    }
}
