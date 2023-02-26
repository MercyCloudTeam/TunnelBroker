<?php

namespace App\Http\Controllers\NodeComponent;

use App\Models\ASN;
use App\Models\Tunnel;
use IPTools\Exception\IpException;
use IPTools\Network;

class FRRController extends NodeComponentBaseController
{

    public array $configure = [
        'asn'
    ];

    /**
     * 配置起手式
     * @param $nodeASN
     * @return string
     */
    public function commandBGP($nodeASN): string
    {
        return "sudo /usr/bin/vtysh -c \" conf t
        router bgp $nodeASN
        ";
    }

    /**
     * 删除bgp配置
     * @param Tunnel $tunnel
     * @return string
     * @throws \Exception
     */
    public function deleteBGP(Tunnel $tunnel): string
    {
        $command = $this->commandBGP($tunnel->node->asn);
        if (isset($tunnel->ip4)){
            $ip4 = (string) Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next()->next();
            $command .= "no neighbor $ip4";
        }
        if(isset($tunnel->ip6)){
            $ip6 = (string) Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next()->next();
            $command .= "no neighbor $ip6";
        }
        $command .= "\"";
        return $command;
    }

    /**
     * 创建BGP
     * @param Tunnel $tunnel
     * @param ASN $ASN
     * @return string
     * @throws IpException
     */
    public function createBGP(Tunnel $tunnel,ASN $asn,int $nodeASN,int $limit = 10): string
    {        //TODO 兼容其他路由器组件、FRR目前还未开发出api只能通过这种方式配置

        $command = $this->commandBGP($nodeASN);
        //使用IP段的第二个可用IP作为对端Peer IP
        if (isset($tunnel->ip4)){
            $inRouteMap = env('IPV6_IN_ROUTEMAP','customer');
            $outRouteMap = env('IPV6_OUT_ROUTEMAP','rpki');
            $v4 = (string) Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next()->next();
            $updateSource = (string) Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next();
            $command .= "
            no neighbor $v4
            neighbor $v4 remote-as $asn->asn
            neighbor $v4 update-source $updateSource
            address-family ipv4
            neighbor $v4 maximum-prefix $limit restart 30
            neighbor $v4 route-map $inRouteMap in
            neighbor $v4 route-map $outRouteMap out
            neighbor $v4 soft-reconfiguration inbound
            neighbor $v4 activate
            exit
            ";
        }

        if(isset($tunnel->ip6)){
            $v6 = (string) Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next()->next();
            $updateSource =  (string) Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next();
            $inRouteMap = env('IPV6_IN_ROUTEMAP','customer');
            $outRouteMap = env('IPV6_OUT_ROUTEMAP','rpki');
            $command .= "
            no neighbor $v6
            neighbor $v6 remote-as $asn->asn
            neighbor $v6 update-source $updateSource
            address-family ipv6
            neighbor $v6 maximum-prefix $asn->limit restart 30
            neighbor $v6 route-map $inRouteMap in
            neighbor $v6 route-map $outRouteMap out
            neighbor $v6 soft-reconfiguration inbound
            neighbor $v6 activate
            exit
            ";
        }
        $command .= "\"";
        return $command;
    }

}
