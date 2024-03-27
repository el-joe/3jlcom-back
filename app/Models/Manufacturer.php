<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Manufacturer extends Model
{
    use HasFactory;

    protected $table = 'manufacturers';

    protected $fillable = [
        'manufacturer',
        'manufacturer_ar',
        'image',
        'status',
        'sequence'

    ];
    protected $hidden = [
        'updated_at'
    ];
    
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function getImageAttribute($image)
    {
        return $image != "" ? url('') . config('global.IMG_PATH') . config('global.MANUFACTURER_IMG_PATH') . $image : '';
    }
}
