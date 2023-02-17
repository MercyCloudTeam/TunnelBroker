<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Http\Controllers\TunnelController;
use App\Models\Tunnel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class TunnelUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public SSH2 $connect;

    public int $connectNode;

    public NodeController $nodeController;
    public TunnelController $tunnelController;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->nodeController = new NodeController();
        $this->tunnelController = new TunnelController();
    }

    /**
     * @throws Exception
     */
    public function getSSHConnect(Tunnel $tunnel)
    {
        if (empty($this->connect) || empty($this->connectNode)) {
            //No Connect
            $this->connect = $this->nodeController->connect($tunnel->node);
            $this->connectNode = $tunnel->node_id;
        } elseif ($this->connectNode != $tunnel->node_id) {
            //Connect Node Changed
            $this->connect->disconnect();
            $this->connect = $this->nodeController->connect($tunnel->node);
            $this->connectNode = $tunnel->node_id;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        Tunnel::where([
            ['status', '!=', 1],
        ])->orderBy('node_id', 'desc')->chunk(50, function ($tunnels) {
            foreach ($tunnels as $tunnel) {
                $this->getSSHConnect($tunnel);
//                Log::debug('TunnelUpdate Processing Tunnel:'.$tunnel->id,[$tunnel,$this->connect]);
                if (!$this->connect->isConnected() || !$this->connect->isAuthenticated()) {
                    Log::debug('TunnelUpdate SSH Connect Failed', [$tunnel, $this->connect]);
                    throw new Exception('SSH Connect Failed');
                }
                switch ($tunnel->status) {
                    case 2:
                    case 6:
                        $this->tunnelController->createTunnel($this->connect,$tunnel);
                        break;
                    case 3:
                        $this->tunnelController->rebuildTunnel($this->connect,$tunnel);
                        break;
                    case 5:
                        $this->tunnelController->changeTunnelIP($this->connect,$tunnel);
                        break;
                    case 7:
                        $this->tunnelController->delTunnel($this->connect,$tunnel);
                        break;
                }
            }
        });
    }
}
