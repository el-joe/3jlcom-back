<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        'city',
        'city_ar',
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
}
