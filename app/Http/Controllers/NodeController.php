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
use IPTools\Exception\IpException;
use IPTools\Network;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Throwable;

class NodeController extends Controller
{

    /**
     * 首页
     * @return \Inertia\Response
     */
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
//        $node = $node->makeVisible(['password','config','login_type','port','username']);
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

    public function initialServerInfo(SSH2 $connect,Node $node)
    {
        //获取系统时间
        $systemTime = $connect->exec("date +'%Y-%m-%d %H:%M:%S'");
        $systemTime = Carbon::parse($systemTime);
        //获取系统版本
        $systemVersion = $connect->exec("cat /etc/os-release | grep PRETTY_NAME | awk -F '=' '{print $2}'");
        $systemVersion = trim($systemVersion, '"');
        //获取系统内核
        $systemKernel = $connect->exec("uname -r");
        //获取系统架构
        $systemArch = $connect->exec("uname -m");
        //获取系统主机名
        $systemHostname = $connect->exec("hostname");
        //获取系统CPU信息
        $systemCPU = $connect->exec("cat /proc/cpuinfo | grep 'model name' | uniq | awk -F ':' '{print $2}'");
        //获取系统内存信息
        $systemMemory = $connect->exec("free -h | grep Mem | awk '{print $2}'");
        //获取系统硬盘信息

    }
    public function updateServerStatus(SSH2 $connect, Node $node)
    {
        //获取CPU使用信息
        $cpuUsage = $connect->exec("top -bn1 | grep load | awk '{printf \"%.2f\", $(NF-2)}'");
        $cpuUsage = (float)$cpuUsage;
        //获取内存使用信息
        $memoryUsage = $connect->exec("free | grep Mem | awk '{printf \"%.2f\", $3/$2*100}'");
        $memoryUsage = (float)$memoryUsage;
        //获取硬盘使用信息
        $diskUsage = $connect->exec("df -h | grep /dev/vda1 | awk '{print $5}'");
        $diskUsage = (float)$diskUsage;
        //获取系统负载信息
        $loadAverage = $connect->exec("cat /proc/loadavg | awk '{print $1,$2,$3}'");
        $loadAverage = explode(' ', $loadAverage);
        $loadAverage = [
            '1' => (float)$loadAverage[0],
            '5' => (float)$loadAverage[1],
            '15' => (float)$loadAverage[2],
        ];

        //获取系统运行时间
        $systemUptime = $connect->exec("uptime -p");
        $systemUptime = Carbon::parse($systemUptime);

    }


    /**
     * 计算流量
     * @param SSH2 $connect
     * @param Node $node
     * @return void
     */
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


    /**
     * 正则匹配流量
     * @param Tunnel $tunnel
     * @param $netDevFile
     * @return void
     */
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

}
