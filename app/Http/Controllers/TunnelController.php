<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Route\FRRController;
use App\Http\Requests\TunnelRequest;
use App\Http\Resources\TunnelResource;
use App\Http\Resources\TunnelsCollectionResource;
use App\Models\ASN;
use App\Models\IPAllocation;
use App\Models\Node;
use App\Models\Tunnel;
use App\Models\TunnelTraffic;
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

        IPAllocation::where('tunnel_id', $tunnel->id)->update(['tunnel_id' => null]);//IP重新进入分配表

        $tunnel->update(['status' => 7]);
//        DeleteTunnel::dispatch($tunnel);
//        $tunnel->delete();
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
        if (!empty($request->asn)) {
            $asn = ASN::find($request->asn);
            if (!$asn->validate || $asn->user_id != $user->id) {
                return throw ValidationException::withMessages([
                    'asn' => ['ASN is not available'],
                ]);
            }
        }
        if ($user->tunnels->count() > env('DEFAULT_USER_LIMIT')) {
            return throw ValidationException::withMessages([
                'tunnel' => ["You've created too many Tunnels"],
            ]);
        }

        switch ($request->mode) {
            case "wireguard":
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
                } catch (\Exception $e) {
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
        return "sudo ip link delete {$tunnel->interface}";
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
                        $id = $tunnel->srcport;//SrcPort作为VXLAN的ID
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
                        $command[] = "echo $localPubKey > $privateKey";
                        $command[] = "echo $localPrivKey > $pub";
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

        if ($port) {
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

    public function delTunnel(SSH2 $ssh, Tunnel $tunnel)
    {
        if ($tunnel->status == 7) {
            $result[] = $ssh->exec($this->deleteTunnelCommand($tunnel));//执行创建Tunnel命令
//            if (!empty($tunnel->asn_id)) {//清理BGP配置
//                $result[] = $ssh->exec((new FRRController())->deleteBGP($tunnel));
//            }
            Log::debug('DelTunnel Exec result', [$result]);
            $tunnel->delete();
        }

    }

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
                    $this->assignIP($tunnel);
                } catch (Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Tunnel IP allocation failed', [$e->getMessage(), $tunnel->toArray()]);
                    $tunnel->update(['status' => 4]);
                    return;
                }
                $tunnel->refresh();//重新加载模型
                $this->createTunnelAction($ssh, $tunnel);

            }
        }

        if ($tunnel->status == 6) {
            $this->createTunnelAction($ssh, $tunnel);
        }
    }

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
        Log::debug("Create Tunnel Result", $result);
        foreach ($result as $item) {
            if (!empty($item)) {
                Log::info("Tunnel($tunnel->id) creation return", [$item, $command]);
//                $tunnel->update(['status' => 6]);
            }
        }
        //执行完成
        $tunnel->update(['status' => 1]);
    }


}
