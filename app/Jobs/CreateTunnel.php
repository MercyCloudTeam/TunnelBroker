<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Http\Controllers\Route\FRRController;
use App\Http\Controllers\TunnelController;
use App\Models\IPAllocation;
use App\Models\Tunnel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use IPTools\Network;
use phpseclib3\Net\SSH2;

class CreateTunnel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function fail($exception = null)
    {
//        \Log::error('Create Tunnel Error', $exception);
    }

    /**
     * 创建操作
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     */
    public function create(SSH2 $ssh,Tunnel $tunnel)
    {

        $result[] = $ssh->exec((new TunnelController())->createTunnelCommand($tunnel));//执行创建Tunnel命令
        $result[] = $ssh->exec("ip link set dev {$tunnel->interface} up");//启动Tunnel
        if (isset($tunnel->ip4) && isset($tunnel->ip6)){//给网口添加地址
            $result[] = $ssh->exec("ip addr add {$tunnel->ip6}/{$tunnel->ip6_cidr} dev {$tunnel->interface}");
            $result[] = $ssh->exec("ip addr add {$tunnel->ip4}/{$tunnel->ip4_cidr} dev {$tunnel->interface}");
        }elseif(isset($tunnel->ip6)){
            $result[] = $ssh->exec("ip addr add {$tunnel->ip6}/{$tunnel->ip6_cidr} dev {$tunnel->interface}");
        }elseif(isset($tunnel->ip4)){
            $result[] = $ssh->exec("ip addr add {$tunnel->ip4}/{$tunnel->ip4_cidr} dev {$tunnel->interface}");
        }
        if (!empty($tunnel->asn_id)){//当需要配置BGP Tunnel
            $bgpResult = $ssh->exec((new FRRController())->createBGP($tunnel));//执行创建Tunnel命令
            if (!empty($bgpResult)){
                \Log::info('创建Tunnel操作时BGP配置出现错误:',[$bgpResult]);
            }
        }
        foreach ($result as $item){
            if (!empty($item)){
                \Log::info('Tunnel创建返回异常',[$item]);
                //TODO 通知管理员
                $tunnel->update(['status'=>6]);
            }
        }

        //执行完成
        $tunnel->update(['status'=>1]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Tunnel::where([
            ['status','=',2],
        ])->chunk(50,function ($tunnels){
            //创建IP规则，从IP池中生成，当tunnel呗删除则清空分配的id，但是已经创建的分配记录不会呗删除，优先使用已被分配的、否则再通过IP池创建新记录
            foreach ($tunnels as $tunnel){
                if (isset($tunnel->ip4) ||isset($tunnel->ip6)){
                    //如果在等待创建期间已经分配了IP的话则删除重新分配
                    IPAllocation::where('tunnel_id',$tunnel->id)->update(['tunnel_id'=>null]);
                }
                $this->ipType($tunnel);
                $tunnel->refresh();//重新加载模型
                $ssh = NodeController::connect($tunnel->node);
                $this->create($ssh,$tunnel);
            }
        });

        Tunnel::where([
            ['status','=',6], //状态异常的再进行一次创建操作
        ])->chunk(50,function ($tunnels){
            foreach ($tunnels as $tunnel){
                $ssh = NodeController::connect($tunnel->node);
                $this->create($ssh,$tunnel);
            }
        });


    }

    protected function ipType(Tunnel $tunnel)
    {
        $v6 =false;
        $v4=false;
        switch ($tunnel->mode){
            case "sit":
                $v6 = true;
                //sit只分配ipv6
                break;
            case "gre":
            case "vxlan": //TODO VXLAN还将分配端口
                //ipv4 + 6 若未指定的情况下v4和v6任一成功分配均可算分配成功
                $v4 = true;
                $v6 = true;
                break;
            case "ipip":
                //ipv4 only
                $v4 = true;
                break;
            default:
                //神秘隧道类型
                $tunnel->update(['status'=>4]);
        }
        $ips = IPAllocation::ofActive($tunnel->node_id);

        $update = [];
        if ($v6){
            $ipv6 = $ips->where('type','ipv6')->limit(1)->get();
            if (!$ipv6->isEmpty()){
                $ipv6 = $ipv6->first();
                $update['ip6']= (string) Network::parse("{$ipv6->ip}/{$ipv6->cidr}")->getFirstIP();
                $update['ip6_cidr']= $ipv6->cidr;
//                $update['ip6_rdns'] = Network::parse($ipv6->ip."1")->r
                $ipv6->update(['tunnel_id'=>$tunnel->id]);
            }
        }
        if ($v4){
            $ipv4 =  $ips->where('type','ipv4')->limit(1)->get();
            if (!$ipv4->isEmpty()){
                $ipv4 = $ipv4->first();
                $update['ip4']=  (string) Network::parse("{$ipv4->ip}/{$ipv4->cidr}")->getFirstIP();
                $update['ip4_cidr']= $ipv4->cidr;
                $ipv4->update(['tunnel_id'=>$tunnel->id]);
            }
        }
        if (!$ips->count()){//IP数量为0
            $update['status']=4;
        }else{
            $update['interface'] = env('TUNNEL_NAME_PREFIX','tun').$tunnel->id;
            $update['local'] = $tunnel->local ?? $tunnel->node->ip;
        }
        $tunnel->update($update);

        //v6默认使用 ::1  v4则按CIDR大小使用第一个IP

    }
}
