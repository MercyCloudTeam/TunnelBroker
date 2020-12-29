<?php

namespace App\Http\Controllers\Route;

use App\Http\Controllers\Controller;
use App\Models\Tunnel;
use IPTools\Network;

class FRRController extends Controller
{

    /**
     * 配置起手式
     * @param $nodeASN
     * @return string
     */
    public function commandBGP($nodeASN)
    {
        return "vtysh -c \" conf t
        router bgp {$nodeASN}
        ";
    }

    /**
     * 删除bgp配置
     * @param Tunnel $tunnel
     * @return string
     * @throws \Exception
     */
    public function deleteBGP(Tunnel $tunnel)
    {
        $command = $this->commandBGP($tunnel->node->asn);
        if (isset($tunnel->ip4)){
            $ip4 = (string) Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
            $command .= "no nei {$ip4}";
        }
        if(isset($tunnel->ip6)){
            $ip6 = (string) Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
            $command .= "no nei {$ip6}";
        }
        $command .= "\"";
        return $command;
    }

    /**
     * 创建BGP
     * @param Tunnel $tunnel
     * @return string
     * @throws \Exception
     */
    public function createBGP(Tunnel $tunnel)
    {        //TODO 兼容其他路由器组件、FRR目前还未开发出api只能通过这种方式配置

        $command = $this->commandBGP($tunnel->node->asn);
        $asn = $tunnel->asn;
        //使用IP段的第二个可用IP作为对端Peer IP
        if (isset($tunnel->ip4)){
            $inRouteMap = env('IPV6_IN_ROUTEMAP','customer');
            $outRouteMap = env('IPV6_OUT_ROUTEMAP','rpki');
            $v4 = (string) Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
            $command .= "
            no nei {$v4}
            nei {$v4} remote-as {$asn->asn}
            add ipv4
            nei {$v4} maximum-prefix {$asn->limit} restart 30
            nei {$v4} route-map {$inRouteMap} in
            nei {$v4} route-map {$outRouteMap} out
            nei {$v4} act
            ";
        }

        if(isset($tunnel->ip6)){
            $v6 = (string) Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
            $inRouteMap = env('IPV6_IN_ROUTEMAP','customer');
            $outRouteMap = env('IPV6_OUT_ROUTEMAP','rpki');
            $command .= "
            no nei {$v6}
            nei {$v6} remote-as {$asn->asn}
            add ipv6
            nei {$v6} maximum-prefix {$asn->limit} restart 30
            nei {$v6} route-map {$inRouteMap} in
            nei {$v6} route-map {$outRouteMap} out
            nei {$v6} act
            ";
        }
        $command .= "\"";
        return $command;
    }


    /**
     * 配置模板
     * @param string $commands
     * @return string
     */
    public function commandTemplate(string $commands)
    {
        return  "vtysh -c \"
            conf t
            {$commands}
        \"";
    }
}
