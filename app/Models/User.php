<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable,MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tunnels()
    {
        return $this->hasMany(Tunnel::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function plan()
    {
        return $this->hasOneThrough(Plan::class, UserPlan::class, 'user_id', 'id', 'id', 'plan_id');
    }

    public function userPlan()
    {
        return $this->hasOne(UserPlan::class, 'user_id', 'id');
    }

    public function asn()
    {
        return $this->hasMany(ASN::class, 'user_id', 'id');
    }

    public function ipAllocation()
    {
        return $this->hasManyThrough(IPAllocation::class, Tunnel::class, 'user_id', 'tunnel_id', 'id', 'id');
    }

    public function bgp()
    {
        return $this->hasMany(BGPSession::class, 'user_id', 'id');
    }
}
