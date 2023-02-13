<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Badcow\DNS\Rdata\Factory;
use Badcow\DNS\{Rdata\PTR, Zone, ResourceRecord, AlignedBuilder, Classes, ZoneBuilder};

class RDNSConfigure implements ShouldQueue
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
        $parent = PTR::reverseIpv6('2001:acad:5889:0:0:0:0');

        $zone = new Zone($parent, 10800);

        $resourceRecords = [
            new ResourceRecord(PTR::reverseIpv6('1', false), Factory::Ptr('gw-01.badcow.co.')),
            new ResourceRecord(PTR::reverseIpv6('2', false), Factory::Ptr('gw-02.badcow.co.')),
            new ResourceRecord(PTR::reverseIpv6('bad', false), Factory::Ptr('badcow.co.')),
            new ResourceRecord(PTR::reverseIpv6('ff', false), Factory::Ptr('mail.badcow.co.'), 3600, Classes::INTERNET),
            new ResourceRecord(PTR::reverseIpv6('aa1', false), Factory::Ptr('esw-01.badcow.co.')),
            new ResourceRecord(PTR::reverseIpv6('aa2', false), Factory::Ptr('esw-02.badcow.co.')),
        ];

        $zone->fromArray($resourceRecords);

        echo AlignedBuilder::build($zone);
    }
}
