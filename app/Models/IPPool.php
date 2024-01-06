<?php

namespace App\Models;

use Isifnet\PieAdmin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class IPPool extends Model
{
	use HasDateTimeFormatter;

    protected  $table = 'ip_pool';

    protected $fillable = [
        'node_id','ip', 'cidr','allocation_size','subnet','ip_type','generated','type'
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    protected $dispatchesEvents = [
      'deleted' => \App\Events\IPPoolDeleted::class,
    ];

    public function ipAllocations()
    {
        return $this->hasMany(IPAllocation::class,'ip_pool_id','id');
    }
}
