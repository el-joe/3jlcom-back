<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'propertys';

    protected $fillable = [
        'category_id',
        'manufacturer_id',
        'model_id',
        'year_id',
        'city_id',
        'area_id',
        'title',
        'description',
        'address',
        'client_address',
        'propery_type',
        'price',
        'title_image',
        'state',
        'country',
        'state',
        'status',
        'total_click',
        'latitude',
        'longitude'

    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];

    protected $appends = [
        'gallery'
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select('id', 'category', 'category_ar', 'manufacturer', 'installment', 'caysh', 'parameter_types', 'image');
    }

    public function manufacturer()
    {
        return $this->hasOne(Manufacturer::class, 'id', 'manufacturer_id')->select('id', 'manufacturer', 'manufacturer_ar', 'image');
    }

    public function model()
    {
        return $this->hasOne(Modell::class, 'id', 'model_id')->select('id', 'model');
    }

    public function year()
    {
        return $this->hasOne(Year::class, 'id', 'year_id')->select('id', 'year');
    }

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id')->select('id', 'city', 'city_ar');
    }

    public function area()
    {
        return $this->hasOne(Area::class, 'id', 'area_id')->select('id', 'area', 'area_ar');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'id', 'added_by');
    }

    public function user()
    {
        return $this->hasMany(User::class, 'id', 'added_by');
    }

    public function assignParameter()
    {
        return  $this->morphMany(AssignParameters::class, 'modal');
    }

    public function parameters()
    {
        return $this->belongsToMany(parameter::class, 'assign_parameters', 'modal_id', 'parameter_id')->withPivot('value','value_ar');;
    }


    public function favourite()
    {
        return $this->hasMany(Favourite::class);
    }

    public function interested_users()
    {
        return $this->hasMany(InterestedUser::class);
    }

    public function advertisement()
    {
        return $this->hasMany(Advertisement::class);
    }

    public function getGalleryAttribute()
    {
        $data = PropertyImages::select('id', 'image')->where('propertys_id', $this->id)->get();

        foreach ($data as $item) {
            if ($item['image'] != '') {
                $item['image'] = $item['image'];
                $item['image_url'] = ($item['image'] != '') ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_GALLERY_IMG_PATH') . $this->id . "/" . $item['image'] : '';
            }
        }
        return $data;
    }
    public function getTitleImageAttribute($image)
    {

        return $image != '' ? url('') . config('global.IMG_PATH') . config('global.PROPERTY_TITLE_IMG_PATH') . $image : '';
    }
    public function getThreeDImageAttribute($threeDimage)
    {
        return $threeDimage != '' ? url('') . config('global.IMG_PATH') . config('global.3D_IMG_PATH') . $threeDimage : '';
    }
    protected $casts = [
        'category_id' => 'integer',
        'manufacturer_id' => 'integer',
        'model_id' => 'integer',
        'year_id' => 'integer',
        "installment" => 'bool',
        'price' => 'integer',
        'installment_price' => 'integer',
        'installment_down' => 'integer',
        //'added_by' => 'integer',
        'total_view' => 'integer',
        'status' => 'integer',
    ];
}
