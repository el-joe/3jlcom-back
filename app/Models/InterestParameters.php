<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestParameters extends Model
{
    use HasFactory;

    protected $table = 'interest_parameters';

    protected $fillable = [

        'modal_id',

    ];

    public function modal()
    {
        return $this->morphTo();
    }
    
    public function parameter()
    {
        return  $this->belongsTo(parameter::class);
    }


    public function getValueAttribute($value)
    {

        $a = json_decode($value, true);
        if ($a == NULL) {
            return $value;
        } else {
            return (json_decode($value, true));
        }
    }

}