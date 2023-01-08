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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IPTools\Network;
use phpseclib3\Net\SSH2;
use Throwable;

class CreateTunnel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public SSH2 $connect;

    public int $connectNode;

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
        \Log::error('Create Tunnels Error', $exception);
    }

    /**
     * 创建操作
     * @param SSH2 $ssh
     * @param Tunnel $tunnel
     * @throws \Exception
     */
    public function create(SSH2 $ssh, Tunnel $tunnel)
    {

        $command = (new TunnelController())->createTunnelCommand($tunnel);
        if (is_array($command)){
            foreach ($command as $cmd){
                $result[] = $ssh->exec($cmd);
            }
        }elseif(is_string($command)){
            $result[] = $ssh->exec($command);
        }
        $result[] = $ssh->exec("sudo ip link set dev {$tunnel->interface} up");//启动Tunnel
        //给网口添加地址
        if (isset($tunnel->ip4) && isset($tunnel->ip6)) {
            $ip6 = (string)Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
            $ip4 = (string)Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add {$ip6}/{$tunnel->ip6_cidr} dev {$tunnel->interface}");
            $result[] = $ssh->exec("sudo ip addr add {$ip4}/{$tunnel->ip4_cidr} dev {$tunnel->interface}");
        } elseif (isset($tunnel->ip6)) {
            $ip6 = (string)Network::parse("{$tunnel->ip6}/{$tunnel->ip6_cidr}")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add {$ip6}/{$tunnel->ip6_cidr} dev {$tunnel->interface}");
        } elseif (isset($tunnel->ip4)) {
            $ip4 = (string)Network::parse("{$tunnel->ip4}/{$tunnel->ip4_cidr}")->getFirstIP()->next();
            $result[] = $ssh->exec("sudo ip addr add {$ip4}/{$tunnel->ip4_cidr} dev {$tunnel->interface}");
        }
        if (!empty($tunnel->asn_id)) {//当需要配置BGP Tunnels
            $bgpResult = $ssh->exec((new FRRController())->createBGP($tunnel));//执行创建Tunnel命令
            if (!empty($bgpResult)) {
                \Log::info('创建Tunnel操作时BGP配置出现错误:', [$bgpResult]);
            }
        }

        Log::debug("Create Tunnel Result", $result);
        foreach ($result as $item) {
            if (!empty($item)) {
                Log::info("Tunnel($tunnel->id) creation return", [$item,$command]);
//                $tunnel->update(['status' => 6]);
            }
        }
        //执行完成
        $tunnel->update(['status' => 1]);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        Tunnel::where([
            ['status', '=', 2],
        ])->chunk(50, function ($tunnels) {
            //创建IP规则，从IP池中生成，当tunnel被删除则清空分配的id，但是已经创建的分配记录不会被删除，优先使用已被分配的、否则再通过IP池创建新记录
            foreach ($tunnels as $tunnel) {
                if (isset($tunnel->ip4) || isset($tunnel->ip6)) {
                    //如果在等待创建期间已经分配了IP的话则删除重新分配
                    IPAllocation::where('tunnel_id', $tunnel->id)->update(['tunnel_id' => null]);
                    $tunnel->update([
                        'ip4' => null,
                        'ip6' => null,
                    ]);
                } else {
                    try {
                        (new TunnelController())->assignIP($tunnel);
                    } catch (Throwable $e) {
                        Log::error('Tunnel IP allocation failed', [$e->getMessage(), $tunnel->toArray()]);
                        $tunnel->update(['status' => 4]);
                        continue;
                    }
                    $tunnel->refresh();//重新加载模型
                    //TODO 优化 复用链接

//                    if (!empty($this->connect))

                    $ssh = NodeController::connect($tunnel->node);

                    $this->create($ssh, $tunnel);
                }
            }
        });

        Tunnel::where([
            ['status', '=', 6], //状态异常的再进行一次创建操作
        ])->chunk(50, function ($tunnels) {
            foreach ($tunnels as $tunnel) {
                $ssh = NodeController::connect($tunnel->node);
                $this->create($ssh, $tunnel);
            }
        });


    }

}
