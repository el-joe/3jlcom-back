<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPurchasedPackage extends Model
{
    use HasFactory;

    protected $casts = [
        'modal_id' => 'int',
        'modal_type' => 'int',
        'package_id' => 'int',
        'used_limit_for_property' => 'int',
        'used_limit_for_advertisement' => 'int',
    ];

    protected $guarded = [];

    public function modal()
    {
        return $this->morphTo();
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
