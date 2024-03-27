<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Year extends Model
{
    use HasFactory;

    protected $table = 'years';

    protected $fillable = [
        'year',
        'status'

    ];
    protected $hidden = [
        'updated_at'
    ];
    
}