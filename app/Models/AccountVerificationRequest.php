<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountVerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id' , 'status'
    ];

    function customer() {
        return $this->belongsTo(Customer::class);
    }
}
