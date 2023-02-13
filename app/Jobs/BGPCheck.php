<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Models\Node;
use App\Models\Tunnel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use IPTools\Network;

class BGPCheck implements ShouldQueue
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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = 'vtysh -c "sh bgp neighbors json"';

        Node::where([
            ['status','=',1],
        ])->chunk(50,function ($nodes){
            //创建IP规则，从IP池中生成，当tunnel呗删除则清空分配的id，但是已经创建的分配记录不会呗删除，优先使用已被分配的、否则再通过IP池创建新记录
            foreach ($nodes as $node){
                $ssh = (new NodeController())->connect($node);
                $result = $ssh->exec( 'vtysh -c "sh bgp neighbors json"');
                $json = json_decode($result);
                if (!empty($json)){
                    $tunnels = Tunnel::where([
                       ['node_id','=',$node->id],
                       ['asn_id','!=',null]
                    ])->get();
                    if (!$tunnels->isEmpty()){
                        $frrList = [];
                        foreach ($json as $k=>$item){
                            //只检查没有的会话，如果因为bug未删除的bgp会话不会进行处理
                            $frrList[] = $k;
                            //TODO:更详细的检测
                        }
                        foreach ($tunnels as $tunnel){
                            $configure = false;
                            if (isset($tunnel->ip4)){
                                $ip4 = (string) Network::parse("$tunnel->ip4/$tunnel->ip4_cidr")->getFirstIP()->next()->next();
                                if (!in_array($ip4,$frrList)){
                                    //FRR里面没找到的
                                    //重新运行配置
                                    $configure = true;
                                }
                            }
                            if(isset($tunnel->ip6)){
                                $ip6 = (string) Network::parse("$tunnel->ip6/$tunnel->ip6_cidr")->getFirstIP()->next()->next();
                                if (!in_array($ip6,$frrList)){
                                    $configure = true;
                                }
                            }
                            if ($configure){
                                BGPConfigure::dispatch($tunnel);
                            }
                        }
                    }
                }
            }
        });
    }
}
