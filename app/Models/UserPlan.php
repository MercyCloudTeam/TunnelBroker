<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;

    protected $table = 'user_plan';

    protected $fillable = [
        'plan_id','user_id','expire_at','reset_day'
    ];

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }
}
