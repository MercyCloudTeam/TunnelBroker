<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Models\Tunnel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use phpseclib3\Net\SSH2;

class TunnelUpdate implements ShouldQueue
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

    public function getSSHConnect(Tunnel $tunnel)
    {
        if (empty($this->connect) || empty($this->connectNode)) {
            //No Connect
            $this->connect = NodeController::connect($tunnel->node);
            $this->connectNode = $tunnel->node_id;
        } elseif ($this->connectNode != $tunnel->node_id) {
            //Connect Node Changed
            $this->connect->disconnect();
            $this->connect = NodeController::connect($tunnel->node);
            $this->connectNode = $tunnel->node_id;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Tunnel::where([
            ['status', '!=', 1],
        ])->orderBy('node_id', 'desc')->chunk(50, function ($tunnels) {
            foreach ($tunnels as $tunnel) {
                $this->getSSHConnect($tunnel);

                if (!$this->connect->isConnected()) {
                    $this->connect = NodeController::connect($tunnel->node);
                }

                switch ($tunnel->status) {
                    case 2:
                    case 6:
                        CreateTunnel::dispatch($tunnel, $this->connect);
                        break;
                    case 5:
                        ChangeTunnelIP::dispatch($tunnel, $this->connect);
                        break;
                    case 7:
                        DeleteTunnel::dispatch($tunnel, $this->connect);
                        break;
                }
            }
        });
    }
}
