<?php

namespace App\Jobs;

use App\Http\Controllers\NIC\RIPEController;
use App\Models\ASN;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ASSETCreate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $values;
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
        if (!empty(env('AS_SET'))){
            $ripe = new RIPEController();
            ASN::where('validate',true)->chunk(50,function ($asn){
                foreach ($asn as $item){
                    $list[] = "AS{$item->asn}";
                }
                if (isset($list)){
                    $this->values[] = implode(", ",$list);
                }
            });

            if (!empty($this->values)){
                foreach ($this->values as $value){
                    $members[] = ['name'=>'members','value'=>$value];
                }
                if (!empty($members)){
                    //无限递归问题，应该定期完全重新生成对象
                    $attribute = [
                        ['name'=>'as-set', 'value'=>env('AS_SET')],
                        ['name'=>'tech-c', 'value'=>env('RIPE_TECH_C')],
                        ['name'=>'admin-c', 'value'=>env('RIPE_ADMIN_C')],
                        ['name'=>'org', 'value'=>env('RIPE_ORGANISATION')],
                        ['name'=>'descr', 'value'=>"MercyCloud TunnelBroker Automatic generated"],
                    ] ;
                    array_splice($attribute,-1,0,$members);
                    $resp = $ripe->update('as-set',env('AS_SET'),$ripe->getAttribute($attribute));
                    if (isset($resp['errormessages'])){
                        \Log::info('RIPE UPDATE ERROR',$resp['errormessages']);
                    }
                }
            }
        }
    }
}
