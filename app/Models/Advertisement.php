<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'start_date', 'end_date', 'type', 'slider_id', 'title', 'category_id', 'image', 'property_id', 'package_id', 'is_enable', 'status'];

    protected $casts = [
        'status' => 'int'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getimageAttribute($image)
    {
        return url('') . config('global.IMG_PATH') . config('global.ADVERTISEMENT_IMAGE_PATH') . $image;
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
