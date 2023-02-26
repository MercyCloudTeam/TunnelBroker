<?php

namespace App\Http\Controllers;

use App\Models\BGPSession;
use App\Models\IPAllocation;
use App\Models\Node;
use App\Models\Tunnel;
use App\Models\TunnelTraffic;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use IPTools\Network;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Throwable;

class NodeController extends Controller
{

    public function index()
    {
        $nodes = Node::all();
        return Inertia::render('Nodes/Index', [
            'nodes' => $nodes,
        ]);
    }

    /**
     * 连接服务器
     * @param Node $node
     * @return SSH2
     * @throws Exception
     */
    public function connect(Node $node): SSH2
    {

        $ssh = new SSH2($node->ip, $node->port, 15);
        switch ($node->login_type) {
            case "password":
                $ssh->login($node->username, $node->password);
                break;
            case "rsa":
                $key = PublicKeyLoader::load($node->password);
                $ssh->login($node->username, $key);
                break;
        }
        if ($ssh->isAuthenticated() && $ssh->isConnected()) {
            //成功的登入
            return $ssh;
        } else {
            //错误的密码将进入这个流程
            //不匹配的验证方式（键盘输入/没有认证）
            //ps：对于这种恶臭登入方式的服务器有什么连接的必有嘛？
            Log::info('Login Server Fail', ['node' => $node->toArray(), 'error' => $ssh->getErrors()]);
            throw new Exception('Login Server Fail');
        }
    }

    public function updateBGPSessionStatus(SSH2 $connect, Node $node)
    {
        if ($node->components->where('component', 'FRR')->where('status', 'active')->isEmpty()) {
            return;
        }
        BGPSession::where('bgp_sessions.status', 1)
            ->join('tunnels', 'tunnels.id', '=', 'bgp_sessions.tunnel_id')
            ->where('tunnels.node_id', $node->id)
            ->chunk(50, function ($bgpSessions) use ($connect) {
                foreach ($bgpSessions as $bgpSession) {
                    $this->updateBGPSessionStatusByTunnel($connect, $bgpSession);
                }
            });
    }

    public function updateBGPSessionStatusByTunnel(SSH2 $connect, BGPSession $session)
    {
        $tunnel = $session->tunnel;
        $updateStatus = [];
        $updateRoute = [];
        if (isset($tunnel->ip4)) {
            $v4 = (string)Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next()->next();
            $v4StatusResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v4 json'");
            $v4ReceivedRouteResult = $connect->exec("sudo /usr/bin/vtysh -c 'show ip bgp neighbors $v4 received-routes json'");
            !$this->isJson($v4StatusResult) ?: $v4StatusResult = json_decode($v4StatusResult, true);
            !$this->isJson($v4ReceivedRouteResult) ?: $v4ReceivedRouteResult = json_decode($v4ReceivedRouteResult, true);
            if (is_array($v4StatusResult)) {
                $updateStatus['v4'] = $v4StatusResult;
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

            $session->update([
                'session' => array_merge($session->session ?? [], $updateStatus),
                'route' => array_merge($session->route ?? [], $updateRoute)
            ]);
        }


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

    public function calculationTraffic(SSH2 $connect, Node $node)
    {
        $command = "cat /proc/net/dev";
        $netDevFile = $connect->exec($command);
        Tunnel::where([
            ['status', '=', 1],
            ['node_id', '=', $node->id]
        ])->chunk(50, function ($tunnels) use ($netDevFile) {
            foreach ($tunnels as $tunnel) {
                $this->pregTraffic($tunnel, $netDevFile);
            }
        });
        //寻找不在数据库记录的Tunnel
        $prefix = env('TUNNEL_NAME_PREFIX', 'tun');
        preg_match_all("/$prefix\d+/", $netDevFile, $tunnelList);
        $dbTunnel = Tunnel::where('node_id', $node->id)->pluck('interface')->toArray();

        if (!empty($tunnelList) && !empty($dbTunnel)) {
            $tunnelList = $tunnelList[0];
            foreach ($tunnelList as $k => $item) {
                if (in_array($item, $dbTunnel)) {
                    unset($tunnelList[$k]);
                }
            }
            //剩下的就是没被记录进数据库的Tunnel了
            if (!empty($tunnelList)) {
                Log::info('Tunnel does not exist in the database:', $tunnelList);
                foreach ($tunnelList as $delTunnel) {
                    //针对数据库没有的Tunnel则删除掉
                    $connect->exec("ip link delete $delTunnel");
                }
            }
        }
    }


    /**
     * @param Tunnel $tunnel
     * @param int $in 系统获取到的
     * @param int $out 系统获取到的
     * @return void
     */
    public function updateTraffic(Tunnel $tunnel, int $in, int $out)
    {
        $cacheName = "$tunnel->interface-traffic";
        $cacheTraffic = json_decode(Cache::get($cacheName), true);
        if (empty($cacheTraffic) || $cacheTraffic['in'] > $in || $cacheTraffic['out'] > $out) {
            //缓存比获取到的大则表面网卡被重启过(那么未捕获到的流量就不计算了)
            $useTrafficIn = $in;
            $useTrafficOut = $out;
//            $startTime = time();
            $cacheTraffic = [
                'in' => $in,
                'out' => $out,
                'start_time' => time(),
                'last_time' => time()
            ];
        } else {
            $useTrafficIn = $in - $cacheTraffic['in'];
            $useTrafficOut = $out - $cacheTraffic['out'];

            $cacheTraffic = array_merge($cacheTraffic, [
                'in' => $in,
                'out' => $out,
                'last_time' => time()
            ]);
//            $startTime = $cacheTraffic['start_time'];

        }
        Cache::put($cacheName, json_encode($cacheTraffic));
//        $startTime = Carbon::createFromTimestamp($startTime);
        //Initial Tunnel Traffic Database Object
        //get lastest tunnel traffic
        $tunnelTraffic = TunnelTraffic::where([
            ['tunnel_id', '=', $tunnel->id],
            ['deadline', '>=', Carbon::now()]
        ])->latest()->first();

        if (empty($tunnelTraffic)) {
            //get user reset day
            $userPlan = $tunnel->user->userPlan;
            $resetDay = $userPlan->reset_day;
            TunnelTraffic::create([
                'user_id' => $tunnel->user->id,
                'tunnel_id' => $tunnel->id,
                'deadline' => Carbon::now()->addMonth()->day($resetDay)->hour(0)->minute(0)->second(0),
                'in' => $useTrafficIn,
                'out' => $useTrafficOut,
            ]);
        } else {
            $tunnelTraffic->update([
                'in' => $tunnelTraffic->in + $useTrafficIn,
                'out' => $tunnelTraffic->out + $useTrafficOut,
            ]);
        }

    }


    public function pregTraffic(Tunnel $tunnel, $netDevFile)
    {
        preg_match("/$tunnel->interface:\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/", $netDevFile, $preg_arr);
        //两个同时为空的情况下，则获取失败
        if (!isset($preg_arr[1]) && !isset($preg_arr[2])) {
            Log::info('Interface not found：', [$tunnel->toArray()]);
            $tunnel->update(['status' => 6]);//找不到的则让服务器重新创建
        } else {
            $thisre = $preg_arr[1];//In
            $thistr = $preg_arr[2];//Out
            $this->updateTraffic($tunnel, $thisre, $thistr);
        }
    }

    public function updateTrafficDB(Tunnel $tunnel, int $in, int $out)
    {
        $tunnelTraffic = TunnelTraffic::where('tunnel_id', $tunnel->id)->latest()->first();

    }


}
