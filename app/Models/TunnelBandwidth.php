<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunnelBandwidth extends Model
{
    use HasFactory;

    protected $table = 'tunnel_bandwidth';

    protected $fillable = [
        'tunnel_id','in','out'
    ];

    public function tunnel()
    {
        return $this->belongsTo('App\Models\Tunnel');
    }
}
