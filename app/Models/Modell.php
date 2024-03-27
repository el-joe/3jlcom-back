<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modell extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'manufacturer_id',
        'model',
        'status'

    ];
    protected $hidden = [
        'updated_at'
    ];


    public function manufacturer(){
        return $this->hasOne(Manufacturer::class,'id','manufacturer_id')->select('id','manufacturer');
    }
    
}
