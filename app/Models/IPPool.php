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
}
