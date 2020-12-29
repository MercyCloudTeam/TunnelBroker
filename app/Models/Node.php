<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Node extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    protected $fillable = [
        'ip','title','username','password','login_type','port',
        'status','limit','bgp','config','asn'
    ];

    protected $hidden = [
        'password','config','login_type','port','username'
    ];



    /**
     * 加密存储密码
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute(string $value)
    {
//        $this->attributes['password'] =  Crypt::encryptString($value);;
        $this->attributes['password'] = encrypt($value);;
    }

    /**
     * 解密获取密码
     *
     * @param string $value
     * @return string
     */
    public function getPasswordAttribute(string $value)
    {
//       return Crypt::decryptString($value);
        return decrypt($value);
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
