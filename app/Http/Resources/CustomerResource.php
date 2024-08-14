<?php

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalViews = Property::where('added_by',$this->id)->sum('total_click');

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'mobile'=>$this->mobile,
            'profile'=>$this->profile,
            'address'=>$this->address,
            'role'=>$this->role,
            'isVerified'=>$this->isVerified ? true : false,
            'isActive'=>$this->isActive ? true : false,
            'about'=>$this->about,
            'instagram_link'=>$this->instagram_link,
            'twitter_link'=>$this->twitter_link,
            'facebook_link'=>$this->facebook_link,
            'customertotalpost'=>$this->customertotalpost,
            'total_views'=> $totalViews,
        ];
    }
}
