<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema()
 */
class Tunnel extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;
    use SoftDeletes;

    protected $fillable = [
        'mode','local','remote','ip4','ip4_cidr','ip6','ip6_cidr','key','ttl','vlan','status','mac','interface',
        'rdns','srcport','dstport','in','out','config','user_id','node_id','asn_id'
    ];



    public function node()
    {
        return $this->belongsTo('App\Models\Node');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function asn()
    {
        return $this->hasOne('App\Models\ASN','id','asn_id');
    }

    public function ips()
    {
        return $this->hasMany('App\Models\IPAllocation','tunnel_id','id');
    }

}
