<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Http\Controllers\Route\FRRController;
use App\Http\Controllers\TunnelController;
use App\Models\Tunnel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BGPConfigure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tunnel;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Tunnel $tunnel)
    {
        $this->tunnel = $tunnel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $command = (new FRRController())->createBGP($this->tunnel);
        $ssh = NodeController::connect($this->tunnel->node);
        $result[] = $ssh->exec($command);//执行创建BGP命令
        \Log::info('BGP配置返回:',[$result,$command,$this->tunnel->id]);
    }
}
