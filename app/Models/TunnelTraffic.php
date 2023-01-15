<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunnelTraffic extends Model
{
    use HasFactory;

    protected $table = 'tunnel_traffic';

    protected $fillable = [
        'tunnel_id','in','out'
    ];

    public function tunnel()
    {
        return $this->belongsTo('App\Models\Tunnel');
    }
}
