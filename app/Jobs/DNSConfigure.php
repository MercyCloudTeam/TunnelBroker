<?php

namespace App\Jobs;

use App\Models\IPAllocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Badcow\DNS\Classes;
use Badcow\DNS\Zone;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\ResourceRecord;
use Badcow\DNS\AlignedBuilder;

class DNSConfigure implements ShouldQueue
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
//        $ips = IPAllocation::whereNotNull('tunnel_id')->get();
//
//        foreach ($ips as $ip){
//            $r = new ResourceRecord;
//            $r->setName(env('TUNNEL_NAME_PREFIX','tun').$ip->tunnel_id);
//            //TODO 生成PTR配置文件
//            $r->setName(Factory::PTR($ip->ip));
//        }
//        $r = new ResourceRecord;
//
//        $builder = new AlignedBuilder();
//        echo $builder->build($zone);

    }
}
