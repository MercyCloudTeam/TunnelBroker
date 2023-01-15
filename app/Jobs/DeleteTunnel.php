<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Http\Controllers\Route\FRRController;
use App\Http\Controllers\TunnelController;
use App\Models\IPAllocation;
use App\Models\IPPool;
use App\Models\Tunnel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use IPTools\Network;
use Log;
use phpseclib3\Net\SSH2;

class DeleteTunnel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tunnel $tunnel;
    public SSH2 $connect;

    /**
     * Create a new job instance.
     *
     * @param Tunnel $tunnel
     */
    public function __construct(Tunnel $tunnel, SSH2 $connect)
    {
        $this->tunnel = $tunnel;
        $this->connect = $connect;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if ($this->tunnel->status == 7){
            $result[] = $this->connect->exec((new TunnelController())->deleteTunnelCommand($this->tunnel));//执行创建Tunnel命令
            if (!empty($this->tunnel->asn_id)) {//清理BGP配置
                $result[] = $this->connect->exec((new FRRController())->deleteBGP($this->tunnel));
            }
            Log::info('exec result', $result);
            $this->tunnel->delete();
        }

    }

}
