<?php

namespace App\Http\Controllers;

use App\Models\IPAllocation;
use App\Models\TunnelTraffic;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getIpAddressUsage(User $user)
    {
        $ipAllocation = $user->ipAllocation;
        //Does not count intranet IPs
        $ipAllocation = $ipAllocation->where('intranet', false);
        $ipv4Usage = $ipAllocation->where('type', 'ipv4')->count();
        $ipv6Usage = $ipAllocation->where('type', 'ipv6')->count();
        return [
            'ipv4' => $ipv4Usage,
            'ipv6' => $ipv6Usage
        ];
    }


    public function getTrfficUsage(User $user)
    {
        $tunnelsTraffics = TunnelTraffic::where([
            ['user_id', $user->id],
            ['deadline', '>', now()],
        ])->get();
//        $tunnels = $user->tunnels->pluck('id');
        $in = 0;
        $out = 0;
        $total = 0;
        foreach ($tunnelsTraffics as $traffic)
        {
            if (!empty($traffic)){
                $in += $traffic->in;
                $out += $traffic->out;
                $total += $traffic->in + $traffic->out;
            }
        }
        return [
            'in' => $in,
            'out' => $out,
            'total' => $total,
        ];
    }

}
