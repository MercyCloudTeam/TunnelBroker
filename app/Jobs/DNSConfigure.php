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

        $zone = new Zone('example.com.');
        $zone->setDefaultTtl(3600);

        $soa = new ResourceRecord;
        $soa->setName('@');
        $soa->setClass(Classes::INTERNET);
        $soa->setRdata(Factory::Soa(
            'example.com.',
            'post.example.com.',
            date('YmdG'),
            3600,
            14400,
            604800,
            3600
        ));
        $ns1 = new ResourceRecord;
        $ns1->setName('@');
        $ns1->setClass(Classes::INTERNET);
        $ns1->setRdata(Factory::Ns('ns1.nameserver.com.'));

        $ns2 = new ResourceRecord;
        $ns2->setName('@');
        $ns2->setClass(Classes::INTERNET);
        $ns2->setRdata(Factory::Ns('ns2.nameserver.com.'));

        $ips = IPAllocation::whereNotNull('tunnel_id')->get();

        foreach ($ips as $ip){
            $r = new ResourceRecord;
            $r->setName(env('TUNNEL_NAME_PREFIX','tun').$ip->tunnel_id);
            $r->setName(Factory::PTR(''));
        }
        $r = new ResourceRecord;


        $zone->addResourceRecord($soa);
        $zone->addResourceRecord($ns1);
        $zone->addResourceRecord($ns2);
        $builder = new AlignedBuilder();
        echo $builder->build($zone);

    }
}
