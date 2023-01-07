<?php

namespace App\Http\Controllers;

use App\Http\Requests\TunnelRequest;
use App\Http\Resources\TunnelResource;
use App\Http\Resources\TunnelsCollectionResource;
use App\Jobs\ChangeTunnelIP;
use App\Jobs\DeleteTunnel;
use App\Models\ASN;
use App\Models\IPAllocation;
use App\Models\Node;
use App\Models\Tunnel;
use App\Rules\TunnelIP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use IPTools\Network;
use Log;


class TunnelController extends Controller
{

    public static array $availableModes = [
        'sit',
        'gre',
        'ipip',
        'ip6gre',
        'ip6ip6',
        'wireguard'
    ];

    /**
     * 详细页面
     * @param Tunnel $tunnel
     * @return \Inertia\Response
     * @throws \Exception
     */
    public function show(Tunnel $tunnel)
    {
        $this->authorize('view', $tunnel);
        if (!empty($tunnel->ip4)) {
            $client_ip4 = (string)Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next()->next();
            $server_ip4 = (string)Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
        }
        if (!empty($tunnel->ip6)) {
            $client_ip6 = (string)Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next()->next();
            $server_ip6 = (string)Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
        }
        return Inertia::render('Tunnels/Show', [
            'asn' => $tunnel->asn,
            'tunnel' => $tunnel,
            'node' => $tunnel->node,
            'client_ip4' => $client_ip4 ?? null,
            'client_ip6' => $client_ip6 ?? null,
            'server_ip4' => $server_ip4 ?? null,
            'server_ip6' => $server_ip6 ?? null,
        ]);
    }

    /**
     * 更新操作
     * @param Request $request
     * @param Tunnel $tunnel
     * @return bool|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateAction(Request $request, Tunnel $tunnel)
    {
        $this->authorize('update', $tunnel);
        Validator::make($request->toArray(), [
            'remote' => ['required', 'ip', new TunnelIP($tunnel->mode, $tunnel->node_id)],
        ])->validateWithBag('updateTunnel');

        if ($tunnel->remote !== $request->remote) {
            //更新请求只针对更新IP
            $status = $tunnel->update([
                'remote' => $request->remote,
                'status' => 5
            ]);
            $tunnel->refresh();//重新加载模型
            ChangeTunnelIP::dispatch($tunnel);//重载tunnel
            return $status;
        }
        return "ERROR";

    }

    /**
     * 更新Tunnel信息
     * @param Request $request
     * @param Tunnel $tunnel
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Tunnel $tunnel)
    {
        $status = $this->updateAction($request, $tunnel);
        if (is_string($status)) {
            return Redirect::back()->with('success', config('status.code' . $status));
        } else {
            return Redirect::route('tunnels.index')->with('success', '修改成功');
        }
    }

    /**
     * 带宽转人话
     * @param $size
     * @return string
     */
    public static function hbw($size)
    {
        $size *= 8;
        if ($size > 1024 * 1024 * 1024) {
            $size = round($size / 1073741824 * 100) / 100 . ' Gbps';
        } elseif ($size > 1024 * 1024) {
            $size = round($size / 1048576 * 100) / 100 . ' Mbps';
        } elseif ($size > 1024) {
            $size = round($size / 1024 * 100) / 100 . ' Kbps';
        } else {
            $size = $size . ' Bbps';
        }
        return $size;
    }

    /**
     * 页面返回
     * @return \Inertia\Response
     */
    public function index()
    {
        $node = Node::where([
            ['status', '!=', 2]
        ])->get();
        $user = Auth::user();
        $asn = ASN::where('user_id', $user->id)->active()->get();
        return Inertia::render('Tunnels/Index', [
            'tunnels' => $user->tunnels,
            'availableMode' => self::$availableModes,
            'asn' => $asn,
            'nodes' => $node,
            //默认显示参数
            // TODO 优化获取默认参数
        ]);
    }

    /**
     * 删除Tunnel
     * @param Tunnel $tunnel
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Tunnel $tunnel)
    {
        $this->authorize('delete', $tunnel);
        //清理IP分配
        DeleteTunnel::dispatch($tunnel);
        IPAllocation::where('tunnel_id', $tunnel->id)->update(['tunnel_id' => null]);//IP重新进入分配表
        $tunnel->delete();
        return Redirect::back()->with('success', "Tunnel删除中");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TunnelRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TunnelRequest $request)
    {
        $status = $this->storeAction($request);
        if (is_string($status)) {
            return Redirect::back()->with('success', config('status.code' . $status));
        } else {
            return Redirect::route('tunnels.index')->with('success', '创建Tunnel成功');
        }

    }

    /**
     * @param TunnelRequest $request
     * @return string
     * @throws ValidationException
     */
    public function storeAction(TunnelRequest $request)
    {
        $node = Node::find($request->node);
        $user = Auth::user();
        if (!empty($request->asn)) {
            $asn = ASN::find($request->asn);
            if (!$asn->validate || $asn->user_id != $user->id) {
                return throw ValidationException::withMessages([
                    'asn' => ['ASN is not available'],
                ]);
            }
        }
        if ($user->tunnels->count() > 5) {
            return throw ValidationException::withMessages([
                'tunnel' => ["You've created too many Tunnels"],
            ]);
        }

        switch ($request->mode){
            case "wireguard":
                $config = [
                    'remote'=>['pubkey'=>$request->pubkey],
                ];
                break;
        }

        $tunnel = Tunnel::create([
            'mode' => $request->mode,
            'remote' => $request->remote,
            'status' => 2,
            'ttl' => 255,//默认将TTL配置成255
            'user_id' => $user->id,
            'node_id' => $node->id,
            'dstport' => $request->port ?? null,
            'config' => $config ?? null,

        ]);

        if ($tunnel) {
            return $tunnel;
        }
        return "ERROR";

    }

    /**
     * 删除Tunnel命令
     * @param Tunnel $tunnel
     * @return string
     */
    public function deleteTunnelCommand(Tunnel $tunnel)
    {
        return "ip link delete {$tunnel->interface}";
    }

    /**
     * 更改Tunnel命令
     * @param Tunnel $tunnel
     * @return string
     */
    public function changeTunnelCommand(Tunnel $tunnel)
    {
        $command = $this->getCommonCommand($tunnel, 'change');
        return $command;
    }

    /**
     * 创建Tunnel命令
     * @param Tunnel $tunnel
     * @return string
     */
    public function createTunnelCommand(Tunnel $tunnel)
    {
        //gre ipip sit用 ip tunnel命令 vxlan需要用 ip link命令
        $command = $this->getCommonCommand($tunnel, 'add');
        return $command;
    }

    /**
     * 获取Tunnel配置命令
     * @param Tunnel $tunnel
     * @param $action
     * @return array|string
     */
    public function getCommonCommand(Tunnel $tunnel, $action)
    {
        switch ($tunnel->mode) {
            case "sit":
            case "gre":
            case "ipip":
            case "ip6gre":
            case "ip6ip6":
                $ipShell = "ip tunnel $action mode $tunnel->mode name $tunnel->interface";
                switch ($action) {
                    case 'add':
                        $ipShell .= " remote $tunnel->remote local $tunnel->local";
                        empty($tunnel->ttl) ?: $ipShell .= " ttl $tunnel->ttl ";
                        empty($tunnel->dstport) ?: $ipShell .= " dstport $tunnel->dstport ";
                        break;
                    case 'change':
                        $ipShell .= "remote $tunnel->remote";
                }
                $command[] = $ipShell;
                break;
            case "vxlan":
                $command[] = "ip link $action $tunnel->interface type $tunnel->mode ";
                break;
            case 'wireguard':
                $privateKey = "/tunnelbroker-wireguard/$tunnel->interface.private";
                $pub = "/tunnelbroker-wireguard/$tunnel->interface-pub";
                $remotePubKey = $tunnel->config['remote']['pubkey'];

                if (!empty($tunnel->ip4)){
                    $allowedIP[] = "$tunnel->ip4/$tunnel->ip4_cidr";
                }
                if (!empty($tunnel->ip6)){
                    $allowedIP[] = "$tunnel->ip6/$tunnel->ip6_cidr";
                }
                $allowedIP = implode(',', $allowedIP);

                $command[] = "ip link add dev $tunnel->interface type wireguard" ;
//                $command[] = "umask 077";
                $command[] = "wg genkey > $privateKey";
                $command[] = "wg pubkey < $privateKey > $pub";
                $command[] = "wg set $tunnel->interface listen-port $tunnel->srcport private-key $privateKey peer $remotePubKey allowed-ips $allowedIP endpoint $tunnel->remote:$tunnel->dstport" ;
                //persistent-keepalive 25
                break;
            default:
                return null;
        }

        Log::debug('TunnelController::getCommonCommand()', $command);
        return $command;
    }

    public function assignPort(Tunnel $tunnel)
    {
        $ports = Tunnel::where([
            ['node_id', $tunnel->node_id],
            ['mode', $tunnel->mode],
        ])->pluck('srcport')->toArray();
        $range = range(10000,65535);
        $available = array_diff($range, $ports);
        if (!empty($available)) {
            return array_shift($available);
        }else{
            return false;
        }
    }

    public function assignVlan(Tunnel $tunnel)
    {
        $vlan = Tunnel::where([
            ['node_id', $tunnel->node_id],
            ['mode', $tunnel->mode],
        ])->pluck('vlan')->toArray();
        $range = range(100,4000);
        $available = array_diff($range, $vlan);
        if (!empty($available)) {
            return array_shift($available);
        }else{
            return false;
        }
    }

    /**
     * @throws \Throwable
     */
    public function assignIP(Tunnel $tunnel)
    {
        $v6 = false;
        $v4 = false;
        $port = false;

        switch ($tunnel->mode) {
            case "sit":
                $v6 = true;
                //sit只分配ipv6
                break;
            case "gre":
                $v4 = true;
                $v6 = true;
                break;
            case "vxlan":
            case "wireguard":
                $v4 = true;
                $v6 = true;
                $port = true;
                break;
            case "ipip":
                //ipv4 only
                $v4 = true;
                break;
            case "ip6ip6":
            case "ip6gre":
                //ipv6 only
                $v6 = true;
                break;
            default:
                //神秘隧道类型
                $tunnel->update(['status' => 7]);
                throw new \Exception('Node IP address exhaustion');
        }

        DB::beginTransaction();
        $ips = IPAllocation::ofActive($tunnel->node_id);
        $update = [];
        if ($ips->count() == 0) {//IP数量为0
            $update['status'] = 4;
            $tunnel->update($update);
            throw new \Exception('Node IP address exhaustion');
        } else {
            $update['interface'] = env('TUNNEL_NAME_PREFIX', 'tun') . $tunnel->id;
        }

        if ($port){
            $port = $this->assignPort($tunnel);
            if (!$port) {
                DB::rollBack();
                throw new \Exception('No available port');
            }
            $update['srcport'] = $port;
        }
        if ($v6) {
            $ipv6 = $ips->where('type', 'ipv6')->limit(1)->get();
            if (!$ipv6->isEmpty()) {
                $ipv6 = $ipv6->first();
                $update['ip6'] = (string)Network::parse("{$ipv6->ip}/{$ipv6->cidr}")->getFirstIP();
                $update['ip6_cidr'] = $ipv6->cidr;
//                $update['ip6_rdns'] = Network::parse($ipv6->ip."1")->r
                $ipv6->update(['tunnel_id' => $tunnel->id]);
            }
        }
        if ($v4) {
            $ipv4 = $ips->where('type', 'ipv4')->limit(1)->get();
            if (!$ipv4->isEmpty()) {
                $ipv4 = $ipv4->first();
                $update['ip4'] = (string)Network::parse("{$ipv4->ip}/{$ipv4->cidr}")->getFirstIP();
                $update['ip4_cidr'] = $ipv4->cidr;
                $ipv4->update(['tunnel_id' => $tunnel->id]);
            }
        }

        try {
            $tunnel->update($update);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        //v6默认使用 ::2  v4则按CIDR大小使用第一个IP
    }

}
