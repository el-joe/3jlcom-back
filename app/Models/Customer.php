<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'firebase_id',
        'mobile',
        'about',
        'profile',
        'address',
        'fcm_id',
        'logintype',
        'isActive',
        'pinterest_link',
        'twitter_link',
        'facebook_link',
        'instagram_link',
    ];

    protected $casts = [
        'isActive' => 'bool',
        'isVerified' => 'bool',
        //'notification' => 'int',
        'subscription' => 'int',
        //'role' => 'int',
    ];

    protected $hidden = [
        'api_token'
    ];

    protected $appends = [
        'customertotalpost'
    ];
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'customer_id' => $this->id
        ];
    }

    public function user_purchased_package()
    {
        return  $this->morphMany(UserPurchasedPackage::class, 'modal');
    }

    function currentPackage()
    {
        $now = now();
        return $this->user_purchased_package()->whereRaw("'$now' between user_purchased_packages.start_date and user_purchased_packages.end_date")->latest()->first();
    }

    function usedPackageAdsLimit()
    {
        return Advertisement::where('customer_id', $this->id)->whereBetween('created_at',[$this->currentPackage()->start_date??now(), $this->currentPackage()->end_date??now()])->count()??0;
    }

    function usedPackagePropertyLimit()
    {
        return Property::where('added_by', $this->id)->whereBetween('created_at',[$this->currentPackage()->start_date??now(), $this->currentPackage()->end_date??now()])->count()??0;
    }

    // public function user_package(){
    //     return $
    // }

    public function getCustomerTotalPostAttribute()
    {
        return Property::where('added_by', (string)$this->id)->get()->count();
    }

    public function favourite()
    {
        return $this->hasMany(Favourite::class, 'user_id');
    }

    public function property()
    {
        return $this->hasMany(Property::class, 'added_by');
    }

    public function getProfileAttribute($image)
    {
        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.USER_IMG_PATH') . $image : '';
    }

    public function usertokens()
    {
        return $this->hasMany(Usertokens::class, 'customer_id');
    }
}
