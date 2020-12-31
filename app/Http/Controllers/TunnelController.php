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
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use IPTools\Network;


class TunnelController extends Controller
{

    /**
     * 详细页面
     * @param Tunnel $tunnel
     * @return \Inertia\Response
     * @throws \Exception
     */
    public function show(Tunnel $tunnel)
    {
        $this->authorize('view', $tunnel);
        if (!empty($tunnel->ip4)){
            $client_ip4 = (string) Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next()->next();
            $server_ip4 = (string) Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
        }
        if (!empty($tunnel->ip6)){
            $client_ip6 = (string) Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next()->next();
            $server_ip6 = (string) Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
        }
        return Inertia::render('Tunnels/Show',[
            'asn'=>$tunnel->asn,
            'tunnel'=>$tunnel,
            'node'=>$tunnel->node,
            'client_ip4'=>$client_ip4 ?? null,
            'client_ip6'=> $client_ip6?? null,
            'server_ip4'=>$server_ip4 ?? null,
            'server_ip6'=> $server_ip6?? null,
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
            'remote'=>['required','ip',new TunnelIP($tunnel->mode,$tunnel->node_id)],
        ])->validateWithBag('updateTunnel');

        if ($tunnel->remote !== $request->remote){
            //更新请求只针对更新IP
            $status = $tunnel->update([
                'remote'=>$request->remote,
                'status'=>5
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
        $status = $this->updateAction($request,$tunnel);
        if (is_string($status)){
            return Redirect::back()->with('success', config('status.code'.$status));
        }else{
            return Redirect::route('tunnels.index')->with('success', '修改成功');
        }
    }

    /**
     * 带宽转人话
     * @param $size
     * @return string
     */
    public static function hbw($size) {
        $size *= 8;
        if($size > 1024 * 1024 * 1024) {
            $size = round($size / 1073741824 * 100) / 100 . ' Gbps';
        } elseif($size > 1024 * 1024) {
            $size = round($size / 1048576 * 100) / 100 . ' Mbps';
        } elseif($size > 1024) {
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
            ['status','!=',2]
        ])->get();
        $user = Auth::user();
        $asn = ASN::where('user_id',$user->id)->active()->get();

        $defaultASN = ($asn->isEmpty() ? null : $asn->first()->id);
        return Inertia::render('Tunnels/Index',[
            'tunnels'=>Auth::user()->tunnels,
            'availableMode'=>[
                'sit'
            ],
            'asn'=>$asn,
            'nodes'=>$node,
            'defaultNode'=>$node->first()->id,
            'defaultASN'=>$defaultASN,
            'defaultMode'=>'sit'
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
        IPAllocation::where('tunnel_id',$tunnel->id)->update(['tunnel_id'=>null]);//IP重新进入分配表
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
        if (is_string($status)){
            return Redirect::back()->with('success', config('status.code'.$status));
        }else{
            return Redirect::route('tunnels.index')->with('success', '创建Tunnel成功');
        }

    }

    /**
     * @param TunnelRequest $request
     * @return string
     */
    public function storeAction(TunnelRequest $request)
    {
        $node = Node::find($request->node);
        $user = Auth::user();
        if (!empty($request->asn)){
            $asn = ASN::find($request->asn);
            if ($asn->validate == false || $asn->user_id != $user->id){
                return "ASN_NO_VALIDATE";
            }
        }
        if ($user->tunnels->count() > $user->limit){
            return "TUNNEL_TOO_MANY";//超过创建Tunnel上限
        }
        $tunnel = Tunnel::create([
            'mode'=>$request->mode,
            'remote'=>$request->remote,
            'local'=>$node->ip,
            'status'=>2,
            'user_id'=>$user->id,
            'node_id'=>$node->id,
            'asn_id'=>$asn->id ?? null,
        ]);
        if ($tunnel){
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
        $command = $this->getCommonCommand($tunnel,'change');
        $command .= "remote {$tunnel->remote}";
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
        $command = $this->getCommonCommand($tunnel,'add');
        $command .= " remote {$tunnel->remote} local {$tunnel->local}";
        empty($tunnel->ttl) ?: $command .= " ttl {$tunnel->ttl} ";
        empty($tunnel->dstport) ?: $command .= " dstport {$tunnel->dstport} ";

        return $command;
    }

    /**
     * 获取Tunnel配置命令
     * @param Tunnel $tunnel
     * @param $action
     * @return string
     */
    public function getCommonCommand(Tunnel $tunnel,$action)
    {
//        switch ($action){
//            case "add":
//        }
        switch ($tunnel->mode) {
            case "sit":
            case "gre":
            case "ipip":
                $command = "ip tunnel {$action} mode {$tunnel->mode} name {$tunnel->interface} ";
                break;
            case "vxlan":
                $command = "ip link {$action} {$tunnel->interface} type {$tunnel->mode} ";
                break;

        }
        return $command;
    }




}
