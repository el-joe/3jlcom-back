<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    function customer() {
        return $this->belongsTo(Customer::class);
    }

    function package() {
        return $this->belongsTo(Package::class);
    }
}
