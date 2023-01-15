<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;

    protected $table = 'user_plan';

    protected $fillable = [
        'plan_id','user_id','expire_at','reset_time'
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'reset_time' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
