<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BGPSession extends Model
{
    /**
     * Status
     * 1 - Normal
     * 2 - Waiting create
     * 3 - Waiting rebuild
     * 4 - Waiting delete
     */

    use HasFactory;

    protected $table = 'bgp_sessions';

    protected $fillable = [
        'asn_id',
        'user_id',
        'tunnel_id',
        'status',
        'limit',
        'session',
        'routes',
        'data',
    ];

    protected $casts = [
        //json
        'data' => 'json',
        'routes' => 'json',
        'session' => 'json',
    ];

    public function tunnel()
    {
        return $this->belongsTo(Tunnel::class);
    }

    public function asn()
    {
        return $this->belongsTo(ASN::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function node()
    {
        return $this->hasOneThrough(Node::class, Tunnel::class, 'id', 'id', 'tunnel_id', 'node_id');
    }


}
