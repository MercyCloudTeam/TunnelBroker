<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name','slug','data','description','limit','ipv6_num','ipv4_num','speed','traffic'
    ];

    protected $casts = [
        'data' => 'json',
    ];

}
