<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class IPAllocation extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ip_allocation';

    protected $fillable = [
        'node_id','ip_pool_id','tunnel_id','ip','cidr','type'
    ];

    public function scopeOfActive($query, $node)
    {
        return $query->where([
            ['node_id','=',$node],
            ['tunnel_id','=',null]
        ]);
    }
}
