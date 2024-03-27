<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'area',
        'area_ar',
        'city_id',
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
