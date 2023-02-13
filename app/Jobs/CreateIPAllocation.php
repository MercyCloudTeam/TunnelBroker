<?php

namespace App\Jobs;

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

class CreateIPAllocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ipPool;

    /**
     * Create a new job instance.
     *
     * @param IPPool $ipPool
     */
    public function __construct(IPPool $ipPool)
    {
        $this->ipPool = $ipPool;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if ($this->ipPool->generated && empty(IPAllocation::where('ip_pool_id', $this->ipPool)->count())) {
            $networks = Network::parse("{$this->ipPool->ip}/{$this->ipPool->cidr}")->moveTo($this->ipPool->allocation_size);
            $chunks = array_chunk($networks, 1000);
            $ipTemplate = [
                'ip_pool_id' => $this->ipPool->id,
                'type' => $this->ipPool->ip_type,
                'node_id' => $this->ipPool->node_id,
                'cidr' => $this->ipPool->allocation_size,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            foreach ($chunks as $chunk) {
                $data = [];
                foreach ($chunk as $network) {
                    $ip = str_replace("/{$this->ipPool->allocation_size}", '', $network);//移除CIDR
                    $ipTemplate['ip'] = $ip;
//                    if ($ipTemplate['type'] == 'ipv6') {
//                        $ipTemplate['last_section'] = substr($ip, strrpos($ip, ':') + 1);
//                    } else {
//                        $ipTemplate['last_section'] = explode('.',$ip)[3];
//                    }
                    $data[] = $ipTemplate;
                }
                DB::table('ip_allocation')->insert($data);
            }

        }
    }

}
