<?php

namespace App\Models;

use App\Http\Controllers\TunnelController;
use Isifnet\PieAdmin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Status
 * 1 - Active
 * 2 - Waiting Create
 * 3 - Rebuilding
 * 4 - Create Error(Waiting Retry)
 * 5 - IP Changed
 * 6 - Error
 * 7 - Waiting Delete
 * @OA\Schema()
 */
class Tunnel extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    protected $casts = [
        'config' => 'array',
    ];

    protected $fillable = [
        'mode', 'local', 'remote', 'ip4', 'ip4_cidr', 'ip6', 'ip6_cidr', 'key', 'ttl', 'vlan', 'status', 'mac', 'interface',
        'rdns', 'srcport', 'dstport', 'in', 'out', 'config', 'user_id', 'node_id'
    ];

    protected $appends = [
        'traffic_cycle'
    ];

    public function local(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->node->ip,
        );
    }

    public function local6(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->node->ip6,
        );
    }

    public function trafficCycle(): Attribute
    {
        return Attribute::make(
            get: function () {
                //Get All Traffic
                $traffic = TunnelTraffic::where([
                    ['tunnel_id', '=', $this->id],
                ])->latest()->first();
                //Sum
                if (empty($traffic)){
                    return [
                        'in' => 0,
                        'in_ht' => "N/A",
                        'out' => 0,
                        'out_ht' => "N/A",
                        'deadline' => 0,
                    ];
                }
                return [
                    'in' => $traffic->in,
                    'in_ht' => TunnelController::ht($traffic->in),
                    'out' => $traffic->out,
                    'out_ht' => TunnelController::ht($traffic->out),
                    'deadline' => $traffic->deadline,
                    'created_at' => $traffic->created_at,
                ];
            }
        );
    }

    public function node()
    {
        return $this->belongsTo('App\Models\Node');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function ips()
    {
        return $this->hasMany('App\Models\IPAllocation', 'tunnel_id', 'id');
    }

    public function traffic()
    {
        return $this->hasMany('App\Models\TunnelTraffic', 'tunnel_id', 'id');
    }


    public function trafficSum(): Attribute
    {
        return Attribute::make(
            get: function () {
                //Get All Traffic
                $traffics = TunnelTraffic::where([
                    ['tunnel_id', '=', $this->id],
                ])->get();
                //Sum
                $in = 0;
                $out = 0;
                foreach ($traffics as $traffic) {
                    $in += $traffic->in;
                    $out += $traffic->out;
                }
                return [
                    'in' => $in,
                    'out' => $out,
                ];
            }
        );
    }

}
