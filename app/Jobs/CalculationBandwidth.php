<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Models\Node;
use App\Models\Tunnel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Log;

class CalculationBandwidth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dbTunnel = [];//被数据库记录的tunnel

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $command = "cat /proc/net/dev";

        $nodes = Node::where([
            ['status', '=', 1],
        ])->get();
        foreach ($nodes as $node) {
            $connect = (new NodeController())->connect($node);
            $netDevFile = $connect->exec($command);
            Tunnel::where([
                ['status', '=', 1],
                ['node_id', '=', $node->id]
            ])->chunk(50, function ($tunnels) use ($netDevFile) {
                foreach ($tunnels as $tunnel) {
                    $this->pregBandwidth($tunnel, $netDevFile);
                }
            });

            //寻找不在数据库记录的Tunnel
            $prefix = env('TUNNEL_NAME_PREFIX', 'tun');
            preg_match_all("/{$prefix}\d+/", $netDevFile, $tunnelList);
//            Log::debug('匹配到的接口：',$tunnelList);
//            Log::debug('数据库接口：',$this->dbTunnel);
            if (!empty($tunnelList) && !empty($this->dbTunnel)) {
                $tunnelList = $tunnelList[0];
                foreach ($tunnelList as $k => $item) {
                    if (in_array($item, $this->dbTunnel)) {
                        unset($tunnelList[$k]);
                    }
                }
                //剩下的就是没被记录进数据库的Tunnel了
                if (!empty($tunnelList)) {
                    Log::info('Tunnel does not exist in the database:', $tunnelList);
                    foreach ($tunnelList as $delTunnel) {
                        //针对数据库没有的Tunnel则删除掉
                        $connect->exec("ip link delete {$delTunnel}");
                    }
                }
            }

        }

    }

    protected function cacheBandwidth($name, $bandwidth, $old)
    {
        //如果网卡重启的过快或次数过多则无法记录该时间段的流量
        if (Cache::has($name)) {//存在
            $cache = Cache::get($name);
            if ($cache > $bandwidth) {//缓存的流量比获取的流量的话则代表网卡重启过
                //将已经缓存的流量计入
                Cache::put($name, $bandwidth);
                return bcadd($old, $cache);
            } elseif ($cache < $bandwidth) {//流量不变的情况下无需更新
                Cache::put($name, $bandwidth);
                //将更新流量计入
                return bcadd($old, bcsub($bandwidth, $cache));
            }
        } else {
            Cache::put($name, $bandwidth);
        }
        return $old;//没有则返回原本
    }

    public function monthBandwidth()
    {
//        if (Carbon::now())
    }


    public function pregBandwidth(Tunnel $tunnel, $netDevFile)
    {
        preg_match("/{$tunnel->interface}:\s+(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/", $netDevFile, $preg_arr);
        //两个同时为空的情况下，则获取失败
        if (!isset($preg_arr[1]) && !isset($preg_arr[2])) {
            Log::info('Interface not found：', [$tunnel->toArray()]);
            $tunnel->update(['status' => 6]);//找不到的则让服务器重新创建
        } else {
            $this->dbTunnel[] = $tunnel->interface;
            $thisre = $preg_arr[1];//In
            $thistr = $preg_arr[2];//Out
//            入网流量
            $in = $this->cacheBandwidth("{$tunnel->interface}-in", $thisre, $tunnel->in);
            $out = $this->cacheBandwidth("{$tunnel->interface}-out", $thistr, $tunnel->out);
            if ($in !== $tunnel->in || $out !== $tunnel->out) { //发生改变才更新
//            todo 20230106 Table Change
                $tunnel->update([
                    'in' => $in,
                    'out' => $out
                ]);
            }
        }
    }


}
