<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Route\FRRController;
use App\Http\Requests\TunnelRequest;
use App\Http\Resources\TunnelResource;
use App\Http\Resources\TunnelsCollectionResource;
use App\Models\ASN;
use App\Models\BGPSession;
use App\Models\IPAllocation;
use App\Models\Node;
use App\Models\NodeComponent;
use App\Models\Tunnel;
use App\Models\TunnelTraffic;
use App\Models\User;
use App\Rules\TunnelIP;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use IPTools\Exception\IpException;
use IPTools\Network;
use Log;
use phpseclib3\Net\SSH2;
use Throwable;


class TunnelController extends Controller
{

    //TODO 动态生成
    public static array $availableModes = [
        'sit',
        'gre',
        'ipip',
        'ip6gre',
        'ip6ip6',
        'wireguard',
        'vxlan'
    ];

    /**
     * 详细页面
     * @param Tunnel $tunnel
     * @return \Inertia\Response
     * @throws Exception
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
            return $status;
        }
        return false;

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
        if (!$status) {
            return Redirect::back()->with('error', '更新失败');
        }
        return Redirect::route('tunnels.index')->with('success', '修改成功');

    }

    /**
     * Byte转人话
     * @param $bytes
     * @return string
     */
    public static function ht($bytes)
    {
        //Byte to human
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
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
            'tunnels' => $user->tunnels->load('node'),
            'availableMode' => self::$availableModes,
            'asn' => $asn,
            'nodes' => $node,
        ]);
    }

    /**
     * 删除Tunnel
     * @param Tunnel $tunnel
     * @return \Illuminate\Http\RedirectResponse
     * @throws Exception
     */
    public function destroy(Tunnel $tunnel)
    {
        $this->authorize('delete', $tunnel);
        //清理IP分配

        IPAllocation::where('tunnel_id', $tunnel->id)->update(['tunnel_id' => null]);//IP重新进入分配表
        $tunnel->update(['status' => 7]);
//        DeleteTunnel::dispatch($tunnel);
//        $tunnel->delete();
        return Redirect::back()->with('success', "Tunnel $tunnel->name Deleted");
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
            return Redirect::route('tunnels.index')->with('success', 'Create Success');
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

        $userTunnelLimit = $user->plan->limit ?? env('DEFAULT_USER_LIMIT');
        if ($user->tunnels->count() >= $userTunnelLimit) {
            return throw ValidationException::withMessages([
                'tunnel' => ["You've created too many Tunnels"],
            ]);
        }
        $addons = [];
        $config['assign_ipv4_address'] = $request->assign_ipv4_address;
        $config['assign_ipv4_intranet_address'] = $request->assign_ipv4_intranet_address;

        switch ($request->mode) {
            case "wireguard":
                //生成加密
                try {
                    $ed25519 = sodium_crypto_sign_keypair();
                    $ed25519PubKey = sodium_crypto_sign_publickey($ed25519);
                    $ed25519PrivKey = sodium_crypto_sign_secretkey($ed25519);
                    $curve25519PubKey = sodium_crypto_sign_ed25519_pk_to_curve25519($ed25519PubKey);
                    $curve25519PrivKey = sodium_crypto_sign_ed25519_sk_to_curve25519($ed25519PrivKey);
                    if (empty($request->pubkey)) {
                        //User not configure pubkey
                        $userEd25519 = sodium_crypto_sign_keypair();
                        $userPubKey = sodium_crypto_sign_publickey($userEd25519);
                        $userPrivKey = sodium_crypto_sign_secretkey($userEd25519);
                        $userPubKeyCurve25519 = sodium_crypto_sign_ed25519_pk_to_curve25519($userPubKey);
                        $userPrivKeyCurve25519 = sodium_crypto_sign_ed25519_sk_to_curve25519($userPrivKey);
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
                    return throw ValidationException::withMessages([
                        'tunnel' => ["WireGuard key generation failed"],
                    ]);
                }

                if (empty($request->pubkey)) {
                    $config = [
                        'remote' => ['pubkey' => base64_encode($userPubKeyCurve25519), 'privkey' => base64_encode($userPrivKeyCurve25519)],
                        'local' => ['pubkey' => base64_encode($curve25519PubKey), 'privkey' => base64_encode($curve25519PrivKey)],
                    ];
                } else {
                    $config = [
                        'remote' => ['pubkey' => $request->pubkey],
                        'local' => ['pubkey' => base64_encode($curve25519PubKey), 'privkey' => base64_encode($curve25519PrivKey)],
                    ];
                }
                break;
            case "vxlan":
                $addons['srcport'] = 4789;
                break;
        }


        $template = [
            'mode' => $request->mode,
            'remote' => $request->remote,
            'status' => 2,
            'ttl' => 255,//默认将TTL配置成255
            'user_id' => $user->id,
            'node_id' => $node->id,
            'dstport' => $request->port ?? null,
            'config' => $config ?? null,

        ];
        $createArr = array_merge($template,$addons);
        $tunnel = Tunnel::create($createArr);

        if (isset($tunnel)) {
            return $tunnel;
        }

        Log::error("Tunnel creation failed", ['request' => $request->all()]);
        return throw ValidationException::withMessages([
            'tunnel' => ["Tunnel creation failed"],
        ]);

    }

    /**
     * 删除Tunnel命令
     * @param Tunnel $tunnel
     * @return string
     */
    public function deleteTunnelCommand(Tunnel $tunnel)
    {
        return "sudo ip link delete $tunnel->interface";
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
                $ipShell = "sudo ip tunnel $action mode $tunnel->mode name $tunnel->interface";
                switch ($action) {
                    case 'add':
                        if ($tunnel->mode == "ip6gre" || $tunnel->mode == "ip6ip6") {
                            $local = $tunnel->local6;
                        } else {
                            $local = $tunnel->local;
                        }
                        $ipShell .= " remote $tunnel->remote local $local";
                        empty($tunnel->ttl) ?: $ipShell .= " ttl $tunnel->ttl ";
                        empty($tunnel->dstport) ?: $ipShell .= " dstport $tunnel->dstport ";
                        break;
                    case 'change':
                        $ipShell .= "remote $tunnel->remote";
                }
                $command[] = $ipShell;
                break;
            case "vxlan":
                switch ($action) {
                    case 'add':
                        //vxlan id = 3 . 后两位user_id . 截取后4位Tunnel ID
                        $id = "3" . substr($tunnel->user_id,-2) . substr($tunnel->id, -4);
                        $ipShell = "sudo ip link add dev $tunnel->interface type vxlan id $id dstport $tunnel->dstport local $tunnel->local remote $tunnel->remote";
                        break;
                    case 'change':
                        $ipShell = "sudo ip link set $tunnel->interface remote $tunnel->remote dstport $tunnel->dstport";
                }
                $command[] = $ipShell;
                break;
            case 'wireguard':
                $remotePubKey = $tunnel->config['remote']['pubkey'];
                $localPubKey = $tunnel->config['local']['pubkey'];
                $localPrivKey = $tunnel->config['local']['privkey'];

                if (!empty($tunnel->ip4)) {
                    $allowedIP[] = "$tunnel->ip4/$tunnel->ip4_cidr";
                }
                if (!empty($tunnel->ip6)) {
                    $allowedIP[] = "$tunnel->ip6/$tunnel->ip6_cidr";
                }
                $allowedIP = implode(',', $allowedIP);

                switch ($action) {
                    case 'add':
                        $privateKey = "/home/tunnelbroker/wireguard-key/$tunnel->interface.private.key";
                        $pub = "/home/tunnelbroker/wireguard-key/$tunnel->interface.pub.key";
                        $command[] = "sudo ip link add dev $tunnel->interface type wireguard";
                        $command[] = "umask 077";
                        $command[] = "echo $localPrivKey > $privateKey";
                        $command[] = "echo $localPubKey > $pub";
                        $command[] = "sudo wg set $tunnel->interface listen-port $tunnel->srcport private-key $privateKey peer $remotePubKey allowed-ips $allowedIP endpoint $tunnel->remote:$tunnel->dstport";
                        break;
                    case 'change':
                        $command[] = "sudo wg set $tunnel->interface listen-port $tunnel->srcport peer $remotePubKey allowed-ips $allowedIP endpoint $tunnel->remote:$tunnel->dstport";
                        break;
                }
                break;
            default:
                return null;
        }

        Log::debug('TunnelController::getCommonCommand()', $command);
        return $command;
    }


//    public function assignVxlanId(Tunnel $tunnel)
//    {
//        $tunnels = Tunnel::where([
//            'mode' => 'vxlan',
//            'node_id' => $tunnel->node_id,
//        ])->pluck('config');
//        $existsIds = [];
//        foreach ($tunnels as $tunnel) {
//            $existsIds[] = $tunnel->config['id'];
//        }
//        //按照协议规定，VXLAN ID的范围是1-16777215
//    }

    public function assignPort(Tunnel $tunnel)
    {
        $ports = Tunnel::where([
            ['node_id', $tunnel->node_id],
        ])->pluck('srcport')->toArray();
        $range = range(10000, 65535);
        $available = array_diff($range, $ports);
        if (!empty($available)) {
            return array_shift($available);
        } else {
            return false;
        }
    }

    public function assignVlan(Tunnel $tunnel)
    {
        $vlan = Tunnel::where([
            ['node_id', $tunnel->node_id],
            ['mode', $tunnel->mode],
        ])->pluck('vlan')->toArray();
        $range = range(100, 4000);
        $available = array_diff($range, $vlan);
        if (!empty($available)) {
            return array_shift($available);
        } else {
            return false;
        }
    }

    /**
     * @throws Throwable
     */
    public function assignIP(Tunnel $tunnel): bool
    {
        $v6 = false;
        $v4 = false;
        $port = false;

        switch ($tunnel->mode) {
            case "gre":
            case "vxlan":
                $v4 = true;
                $v6 = true;
                break;
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
            case "sit":
                //ipv6 only
                $v6 = true;
                break;
            default:
                //神秘隧道类型
                $tunnel->update(['status' => 7]);
                Log::info('TunnelController::assignIP() Unknown tunnel mode', ['tunnel' => $tunnel]);
                throw new Exception('Node IP address exhaustion');
        }

        //Check Ip Address Limit
        $user = User::find($tunnel->user_id);
        $plan = $user->plan;
        $ipv4Limit = $plan->ipv4_num;
        $ipv6Limit = $plan->ipv6_num;
        $usageIPAddress = (new UserController())->getIpAddressUsage($user);
        if ($usageIPAddress['ipv4'] >= $ipv4Limit) {
            $tunnel->update(['status' => 4]);
            return false;
        }
        if ($usageIPAddress['ipv6'] >= $ipv6Limit) {
            $tunnel->update(['status' => 4]);
            return false;
        }

        //根据用户需求 分配IP地址
        //默认分配内网IPv4地址
        $assignIpv4IntranetAddress = $tunnel->config['assign_ipv4_intranet_address'] ?? true;
        //默认不分配
        $assignIpv4Address = $tunnel->config['assign_ipv4_address'] ?? false;

        if (!$assignIpv4Address){
            //如果不分配，把变量也为false
            $v4 = false;
        }

        DB::beginTransaction();
        $ips = IPAllocation::ofActive($tunnel->node_id);
//        if (!$assignIntranetAddress) {
//            $ips = $ips->where('intranet', false);
//        }
        $ips = $ips->get();
        $update = [];
        $update['interface'] = env('TUNNEL_NAME_PREFIX', 'tun') . $tunnel->id;

        switch ($tunnel->mode){
            case "wireguard":
                $update['srcport'] = 4789;
                break;
        }

        if ($port) {
            $port = $this->assignPort($tunnel);
            if (empty($port)) {
                Log::info('TunnelController::assignIP() Port exhaustion', ['tunnel' => $tunnel]);
                DB::rollBack();
                return false;
            }
            $update['srcport'] = $port;
        }

        if ($v6) {
            $ipv6 = $ips->where('type', 'ipv6')->first();
            if (!empty($ipv6)) {
                $update['ip6'] = (string)Network::parse("$ipv6->ip/$ipv6->cidr")->getFirstIP();
                $update['ip6_cidr'] = $ipv6->cidr;
                IPAllocation::where('id', $ipv6->id)->update(['tunnel_id' => $tunnel->id]);
            } else {
                DB::rollBack();
                Log::info('TunnelController::assignIP() IPv6 address exhaustion', ['tunnel' => $tunnel]);
                return false;
            }
        }
        if ($v4) {
            if ($assignIpv4IntranetAddress) {
                $ipv4 = $ips->where('type', 'ipv4')->where('intranet', true)->first();
            } else {
                $ipv4 = $ips->where('type', 'ipv4')->where('intranet', false)->first();
            }

            if (!empty($ipv4)) {
                $update['ip4'] = (string)Network::parse("$ipv4->ip/$ipv4->cidr")->getFirstIP();
                $update['ip4_cidr'] = $ipv4->cidr;
                IPAllocation::where('id', $ipv4->id)->update(['tunnel_id' => $tunnel->id]);
            } else {
                DB::rollBack();
                Log::info('TunnelController::assignIP() IPv4 address exhaustion', ['tunnel' => $tunnel]);
                return false;
            }
        }


        try {
            $tunnel->update($update);
        } catch (Exception $e) {
            Log::info('TunnelController::assignIP() Update tunnel failed', ['tunnel' => $tunnel]);
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;
        //v6默认使用 ::2  v4则按CIDR大小使用第一个IP
    }

    /**
     * 删除隧道
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @return void
     */
    public function delTunnel(SSH2 $ssh, Tunnel $tunnel)
    {
        switch ($tunnel->status) {
            case 7:
                //Del Tunnel
                $result[] = $ssh->exec($this->deleteTunnelCommand($tunnel));
                Log::debug('DelTunnel Exec result', [$result]);
                $tunnel->delete();
                break;
            case 3:
                //Rebuild Tunnel
                $ssh->exec($this->deleteTunnelCommand($tunnel));
                break;
        }
    }

    public function createBGPSession(SSH2 $ssh, BGPSession $bgpSession)
    {
        $frrController = new \App\Http\Controllers\NodeComponent\FRRController();
        $tunnel = $bgpSession->tunnel;
        $asn = $bgpSession->asn;
        $limit = $bgpSession->limit ?? $asn->limit;
        $nodeComponent = NodeComponent::where('node_id', $tunnel->node_id)->where('component', 'FRR')->first();
        if (!empty($nodeComponent) && !empty($nodeComponent->data)) {
            $nodeASN = $nodeComponent->data['asn'];
            if (empty($nodeASN)) {
                Log::error('Create BGP Session,Node ASN Not Found', $bgpSession->toArray());
            }
            $command = $frrController->createBGP($tunnel, $asn, $nodeASN, $limit);
            Log::debug('Create BGP Session', [$command]);
            $result = $ssh->exec($command);
            if (empty($result)) {
                $bgpSession->update([
                    'status' => 1
                ]);
            } else {
                $bgpSession->update([
                    'status' => 4
                ]);
                Log::info('Create BGP Session,Exec Result', [$result]);
            }


        } else {
            Log::info('Create BGP Session,Node Component Not Found', $bgpSession->toArray());
        }
//        $node = $tunnel->node;
//        $frrController->createBGP($tunnel,$asn)

    }

    /**
     * 删除BGP Session
     * @param SSH2 $ssh
     * @param BGPSession $bgpSession
     * @return void
     * @throws Exception
     */
    public function delBGPSession(SSH2 $ssh, BGPSession $bgpSession)
    {
        $frrController = new \App\Http\Controllers\NodeComponent\FRRController();
        $tunnel = $bgpSession->tunnel;
        $nodeComponent = NodeComponent::where('node_id', $tunnel->node_id)->where('component', 'FRR')->first();
        if (!empty($nodeComponent) && !empty($nodeComponent->data)) {
            $nodeASN = $nodeComponent->data['asn'];
            if (empty($nodeASN)) {
                Log::error('Del BGP Session,Node ASN Not Found', $bgpSession->toArray());
            }
            $command = $frrController->deleteBGP($tunnel, $nodeASN);
            Log::debug('Del BGP Session', [$command]);
            $result = $ssh->exec($command);
            if (empty($result)) {
                switch ($bgpSession->status) {
                    case 3:
                        $bgpSession->update([
                            'status' => 2
                        ]);
                        break;
                    case 4:
                        $bgpSession->delete();
                        break;

                }
            } else {
                Log::info('Del BGP Session,Exec Result', [$result]);
            }
        }
    }

    /**
     * 更改隧道IP
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @return void
     */
    public function changeTunnelIP(SSH2 $ssh, Tunnel $tunnel)
    {
        if ($tunnel->status == 5) {
            $result = [];
            $command = $ssh->exec($this->changeTunnelCommand($tunnel));
            if (is_array($command)) {
                foreach ($command as $cmd) {
                    $result[] = $ssh->exec($cmd);
                }
            } elseif (is_string($command)) {
                $result[] = $ssh->exec($command);
            }

            Log::debug('ChangeTunnelIp Exec result', [$result, $command]);
            $tunnel->update(['status' => 1]);
        }
    }


    /**
     * 重建隧道
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @return void
     * @throws IpException
     */
    public function rebuildTunnel(SSH2 $ssh, Tunnel $tunnel)
    {
        $this->delTunnel($ssh, $tunnel);
        $this->createTunnelAction($ssh, $tunnel);
    }

    /**
     * 创建隧道
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @return void
     * @throws IpException
     */
    public function createTunnel(SSH2 $ssh, Tunnel $tunnel)
    {
        if ($tunnel->status == 2) {
            if (isset($tunnel->ip4) || isset($tunnel->ip6)) {
                //如果在等待创建期间已经分配了IP的话则删除重新分配
                IPAllocation::where('tunnel_id', $tunnel->id)->update(['tunnel_id' => null]);
                $tunnel->update([
                    'ip4' => null,
                    'ip6' => null,
                ]);
            } else {
                try {
                    $bool = $this->assignIP($tunnel);
                } catch (Throwable $e) {
                    Log::error('Create Tunnel,Assign IP Error', [$e->getMessage(), $tunnel->toArray()]);
                    $tunnel->update(['status' => 4]);
                    return;
                }
                $tunnel->refresh();//重新加载模型
                if ($bool) {
                    $this->createTunnelAction($ssh, $tunnel);
                }else{
                    $tunnel->update(['status' => 4]);
                }

            }
        }

        if ($tunnel->status == 6) {
            $this->createTunnelAction($ssh, $tunnel);
        }
    }

    /**
     * 删除隧道
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @return void
     * @throws \IPTools\Exception\IpException
     */
    public function createTunnelAction(SSH2 $ssh, Tunnel $tunnel)
    {
        $command = $this->createTunnelCommand($tunnel);
        if (is_array($command)) {
            foreach ($command as $cmd) {
                $result[] = $ssh->exec($cmd);
            }
        } elseif (is_string($command)) {
            $result[] = $ssh->exec($command);
        }
        $result[] = $ssh->exec("sudo ip link set dev $tunnel->interface up");//启动Tunnel
        //给网口添加地址
        if (isset($tunnel->ip4) && isset($tunnel->ip6)) {
            $ip6 = (string)Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next();
            $ip4 = (string)Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add $ip6/{$tunnel->ip6_cidr} dev $tunnel->interface");
            $result[] = $ssh->exec("sudo ip addr add $ip4/{$tunnel->ip4_cidr} dev $tunnel->interface");
        } elseif (isset($tunnel->ip6)) {
            $ip6 = (string)Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add $ip6/$tunnel->ip6_cidr dev $tunnel->interface");
        } elseif (isset($tunnel->ip4)) {
            $ip4 = (string)Network::parse("$tunnel->ip4/{$tunnel->ip4_cidr}")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add $ip4/$tunnel->ip4_cidr dev $tunnel->interface");
        }
        //把网口扔到VRF里面
        $result[] = $ssh->exec("sudo ip link set dev {$tunnel->interface} master TunnelBrokerIO");

        //Speed Limit
        $plan = User::find($tunnel->user_id)->plan;
        $speed = $plan->speed;
        if ($speed > 0) {
            if ($tunnel->status != 2) {
                $result[] = $ssh->exec("tc qdisc del dev $tunnel->interface ingress");
            }
            $result[] = $ssh->exec("sudo tc qdisc add dev $tunnel->interface ingress handle ffff:");
            $result[] = $ssh->exec("sudo tc filter add dev $tunnel->interface parent ffff: protocol all prio 1 basic police rate {$speed}Mbit burst 10Mbit mtu 65535 drop");
        }

//        Log::debug("Create Tunnel Result", [$result]);
        foreach ($result as $item) {
            if (!empty($item)) {
                Log::info("Tunnel($tunnel->id) creation return", [$item]);
            }
        }
        //执行完成
        $tunnel->update(['status' => 1]);
    }

    /**
     * 重建隧道
     * @param Tunnel $tunnel
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function rebuild(Tunnel $tunnel)
    {
        $this->authorize('update', $tunnel);
        $tunnel->status = 3;
        $tunnel->save();
        return Redirect::back()->with('success', "Tunnel $tunnel->name Rebuild");

    }


}
