<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Node extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    protected $fillable = [
        'ip','title','username','password','login_type','port',
        'status','limit','config','public_ip','ip6','public_ip6'
    ];

    protected $hidden = [
        'password','config','login_type','port','username'
    ];

    public function password(): Attribute
    {
        return new Attribute(
            get: fn($value) =>decrypt($value),
            set: fn($value) =>encrypt($value)
        );
    }

    protected $casts = [
        'config' => 'json',
    ];

    /**
     * 模型绑定
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tunnels()
    {
        return $this->hasMany('App\Models\Tunnel');
    }




}
