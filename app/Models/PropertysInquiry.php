<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertysInquiry extends Model
{
    use HasFactory;

    protected $table ='propertys_inquiry';

    protected $fillable = [
        'propertys_id',
        'customers_id',
        'offer',
        'status'
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at'
    ];
    protected $casts = [
        'status' => 'bool',
        'offer' => 'integer',
    ];

    public function customer(){
        return $this->hasOne(Customer::class,'id','customers_id')->select('id','name','email','mobile','address','fcm_id','notification');
    }

    public function property(){
        return $this->hasOne(Property::class,'id','propertys_id')->with('category')->with('city')->with('manufacturer')->with('model')->with('year')->with('customer:id,name,mobile');
    }

}

