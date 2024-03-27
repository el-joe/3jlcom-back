<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    use HasFactory;
    protected $table ='user_interests';

    protected $fillable = [
        'user_id',
        'category_ids',
    ];
    protected $hidden = [
        'updated_at',
    ];


    public function customer(){
        return $this->hasOne(Customer::class,'id','customers_id')->select('id','name','email','mobile','address','fcm_id','notification');
    }

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

    public function city()
    {
        return $this->hasOne(City::class, 'id', 'city_id')->select('id', 'city', 'city_ar');
    }

    public function area()
    {
        return $this->hasOne(Area::class, 'id', 'area_id')->select('id', 'area', 'area_ar');
    }
}
