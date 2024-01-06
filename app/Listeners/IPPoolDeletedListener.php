<?php

namespace App\Listeners;

use App\Events\IPPoolDeleted;
use App\Models\IPAllocation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class IPPoolDeletedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(IPPoolDeleted $event): void
    {
        $ipPool = $event->ipPool;
        IPAllocation::where('ip_pool_id', $ipPool->id)->delete();
    }
}