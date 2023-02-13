<?php

namespace App\Jobs;

use App\Http\Controllers\NodeController;
use App\Models\Node;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class NodeUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public NodeController $nodeController;
    public int $connectNode;
    public SSH2 $connect;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->nodeController = new NodeController();
    }

    /**
     * @throws Exception
     */
    public function getSSHConnect(Node $node)
    {
        if (empty($this->connect) || empty($this->connectNode)) {
            //No Connect
            $this->connect = $this->nodeController->connect($node);
            $this->connectNode = $node->id;
        } elseif ($this->connectNode != $node->id) {
            //Connect Node Changed
            $this->connect->disconnect();
            $this->connect = $this->nodeController->connect($node);
            $this->connectNode =  $node->id;
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
        Node::where([
            ['status','=',1],
        ])->chunk(50,function ($nodes){
            foreach ($nodes as $node){
                //Connect
                $this->getSSHConnect($node);
                if (!$this->connect->isConnected() || !$this->connect->isAuthenticated()) {
                    Log::debug('NodeUpdate SSH Connect Failed', [$node, $this->connect]);
                    throw new Exception('SSH Connect Failed');
                }
                //Update Traffic
                $this->nodeController->calculationTraffic($this->connect,$node);
            }
        });
    }
}
