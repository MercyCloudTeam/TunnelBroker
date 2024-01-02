<?php

namespace App\Models;

use Isifnet\PieAdmin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class NodeConnect extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'node_connect';
    
}
