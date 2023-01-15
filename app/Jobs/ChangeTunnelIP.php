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
use phpseclib3\Net\SSH2;

class ChangeTunnelIP implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tunnel $tunnel;
    public SSH2 $connect;

    /**
     * Create a new job instance.
     *
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
        if ($this->tunnel->status == 5){
            $result = [];
            $command =$this->connect->exec((new TunnelController())->changeTunnelCommand($this->tunnel));
            if (is_array($command)) {
                foreach ($command as $cmd) {
                    $result[] = $this->connect->exec($cmd);
                }
            } elseif (is_string($command)) {
                $result[] = $this->connect->exec($command);
            }

            Log::debug('ChangeTunnelIp Exec result', [$result, $command]);
            $this->tunnel->update(['status' => 1]);
        }
    }

}
