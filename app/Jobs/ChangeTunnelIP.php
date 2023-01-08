<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
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

class ChangeTunnelIP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tunnel;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
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
            ['status', '=', 5],
        ])->chunk(50, function ($tunnels) {
            foreach ($tunnels as $tunnel) {
                $ssh = NodeController::connect($tunnel->node);
                $command = $ssh->exec((new TunnelController())->changeTunnelCommand($this->tunnel));
                if (is_array($command)){
                    foreach ($command as $cmd){
                        $result[] = $ssh->exec($cmd);
                    }
                }elseif(is_string($command)){
                    $result[] = $ssh->exec($command);
                }
                Log::debug('Exec result', $result);

            }
        });
        $ssh = NodeController::connect($this->tunnel->node);
        $result[] = $ssh->exec((new TunnelController())->changeTunnelCommand($this->tunnel));//执行创建Tunnel命令
        $this->tunnel->update(['status' => 1]);
    }

}
