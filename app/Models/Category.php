<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'category',
        'category_ar',
        'manufacturer',
        'installment',
        'caysh',
        'find',
        'image',
        'status',
        'sequence',
        'parameter_types'

    ];
    protected $hidden = [
        'updated_at'
    ];
    protected $casts = [
        'manufacturer' => 'bool',
        'caysh' => 'bool',
        'find' => 'bool',
        'installment' => 'bool'
    ];

    public function parameter()
    {
        return $this->hasMany(parameter::class);
    }
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function getImageAttribute($image)
    {
        return $image != "" ? url('') . config('global.IMG_PATH') . config('global.CATEGORY_IMG_PATH') . $image : '';
    }
}
