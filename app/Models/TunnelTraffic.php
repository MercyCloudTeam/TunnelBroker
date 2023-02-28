<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TunnelTraffic extends Model
{
    use HasFactory;

    protected $table = 'tunnel_traffic';

    protected $casts = [
        'deadline' => 'datetime',
    ];

    protected $fillable = [
        'tunnel_id', 'in', 'out', 'deadline','user_id'
    ];

    public function tunnel()
    {
        return $this->belongsTo('App\Models\Tunnel');
    }
}
