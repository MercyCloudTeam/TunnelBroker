<?php

namespace App\Http\Controllers\NodeComponent;

use App\Models\ASN;
use App\Models\BGPSession;
use App\Models\Node;
use App\Models\Tunnel;
use Illuminate\Support\Facades\Log;
use IPTools\Exception\IpException;
use IPTools\Network;
use phpseclib3\Net\SSH2;

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
    public function deleteBGP(Tunnel $tunnel,int $nodeASN): string
    {
        $command = $this->commandBGP($nodeASN);
        if (isset($tunnel->ip4)){
            $ip4 = (string) Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next()->next();
            $command .= "no neighbor $ip4".PHP_EOL;
        }
        if(isset($tunnel->ip6)){
            $ip6 = (string) Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next()->next();
            $command .= "no neighbor $ip6".PHP_EOL;
        }
        $command .= "\"";
        return $command;
    }

    /**
     * 创建BGP
     * @param Tunnel $tunnel
     * @param ASN $asn
     * @param int $nodeASN
     * @param int $limit
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



    /**
     * 获取BGP Session信息
     * @param SSH2 $connect
     * @param Node $node
     * @return void
     * @throws IpException
     */
    public function updateBGPSessionStatus(SSH2 $connect, Node $node)
    {
        if ($node->components->where('component', 'FRR')->where('status', 'active')->isEmpty()) {
            return;
        }
        BGPSession::where('bgp_sessions.status', 1)
            ->join('tunnels', 'tunnels.id', '=', 'bgp_sessions.tunnel_id')
            ->where('tunnels.node_id', $node->id)
            ->select('*', 'bgp_sessions.id as bgp_session_id')
            ->chunk(50, function ($bgpSessions) use ($connect) {
                foreach ($bgpSessions as $bgpSession) {
                    $this->updateBGPSessionStatusByTunnel($connect, $bgpSession);
                }
            });
    }

    /**
     * 获取Tunnel BGP Session 信息
     * @param SSH2 $connect
     * @param BGPSession $session
     * @return void
     * @throws \IPTools\Exception\IpException
     */
    public function updateBGPSessionStatusByTunnel(SSH2 $connect, BGPSession $session)
    {
        $tunnel = $session->tunnel;
        $updateStatus = [];
        $updateRoute = [];
        if (isset($tunnel->ip4)) {
            $v4 = (string)Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next()->next();
            $v4StatusResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v4 json'");
            $v4ReceivedRouteResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v4 received-routes json'");
            !$this->isJson($v4StatusResult) ?: $v4StatusResult = json_decode($v4StatusResult, true);
            !$this->isJson($v4ReceivedRouteResult) ?: $v4ReceivedRouteResult = json_decode($v4ReceivedRouteResult, true);
            if (is_array($v4StatusResult)) {
                $updateStatus['v4'] = $v4StatusResult;
                if (isset($v4StatusResult['bgpNoSuchNeighbor'])) {
                    Log::debug('Update BGPSession Status Fail(no such neighbor) $v4StatusResult',
                        ['bgpSession' => $session->toArray(), 'error' => $v4StatusResult]
                    );
                    $status = 2;
                }
            } else {
                Log::info('Update BGPSession Status Fail(json decode fail) $v4StatusResult',
                    ['bgpSession' => $session->toArray(), 'error' => $v4StatusResult]
                );
            }
            if (is_array($v4ReceivedRouteResult)) {
                $updateRoute['v4'] = $v4ReceivedRouteResult;
            } else {
                Log::info('Update BGPSession Status Fail(json decode fail) $v4ReceivedRouteResult',
                    ['bgpSession' => $session->toArray(), 'error' => $v4ReceivedRouteResult]
                );
            }
        }
        if (isset($tunnel->ip6)) {
            $v6 = (string)Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next()->next();
            $v6StatusResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v6 json'");
            $v6ReceivedRouteResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v6 received-routes json'");
            !$this->isJson($v6StatusResult) ?: $v6StatusResult = json_decode($v6StatusResult, true);
            !$this->isJson($v6ReceivedRouteResult) ?: $v6ReceivedRouteResult = json_decode($v6ReceivedRouteResult, true);
            if (is_array($v6StatusResult)) {
                $updateStatus['v6'] = $v6StatusResult;
                if (isset($v6StatusResult['bgpNoSuchNeighbor'])) {
                    Log::debug('Update BGPSession Status Fail(no such neighbor) $v6StatusResult',
                        ['bgpSession' => $session->toArray(), 'error' => $v6StatusResult]);
                    $status = 2;
                }
            } else {
                Log::info('Update BGPSession Status Fail(json decode fail) $v6StatusResult',
                    ['bgpSession' => $session->toArray(), 'error' => $v6StatusResult]
                );
            }
            if (is_array($v6ReceivedRouteResult)) {
                $updateRoute['v6'] = $v6ReceivedRouteResult;
            } else {
                Log::info('Update BGPSession Status Fail(json decode fail) $v6ReceivedRouteResult',
                    ['bgpSession' => $session->toArray(), 'error' => $v6ReceivedRouteResult]
                );
            }
        }
        BGPSession::find($session->bgp_session_id)->update([
            'session' => $updateStatus,
            'route' => $updateRoute,
            'status' => $status ?? 1
        ]);

//        $newSessionStatus = [];
//        foreach ($result as $k=>$v){
//            $jsonDecode = json_decode($v, true);
//            if (empty($jsonDecode)){
//                Log::info('Update BGPSession Status Fail(json decode fail)', ['bgpSession' => $session->toArray(), 'error' => $v]);
//            }else{
//                $newSessionStatus[$k] = $jsonDecode;
//            }
//        }
//        $session->update([
//            'session'
//        ]);
    }

}
