<?php

namespace App\Models;

use Isifnet\PieAdmin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class IPAllocation extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ip_allocation';

    protected $fillable = [
        'node_id','ip_pool_id','tunnel_id','ip','cidr','type','intranet'
    ];

    public function scopeOfActive($query, $node)
    {
        return $query->where([
            ['node_id','=',$node],
            ['tunnel_id','=',null]
        ]);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function ip_pool()
    {
        return $this->belongsTo(IPPool::class);
    }

    public function tunnel()
    {
        return $this->belongsTo(Tunnel::class);
    }
}
