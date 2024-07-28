<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $casts = [
        'duration' => 'int',
        'price' => 'int',
        'status' => 'int',
        'property_limit' => 'int',
        'advertisement_limit' => 'int'
    ];
}
