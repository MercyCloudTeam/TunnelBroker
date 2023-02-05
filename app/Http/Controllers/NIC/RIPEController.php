<?php

namespace App\Http\Controllers\NIC;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use phpseclib3\Net\SSH2;

class RIPEController extends Controller
{
    public  $url ;

    public function __construct()
    {
        if (env('USE_RIPE_TEST_API')){//是否使用测试API
            $this->url = "https://rest-test.db.ripe.net/ripe";
        }
        $this->url = "https://rest.db.ripe.net/ripe";

    }

    /**
     * 更新操作
     * @param $objectType
     * @param $key
     * @param $params
     * @return \Illuminate\Http\Client\Response
     */
    public function update($objectType,$key,$params)
    {
        $password = env('RIPE_PASSWORD');
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->put("$this->url/$objectType/$key?password=$password",$params);
    }


    /**
     * 获取请求Attribute
     * @param array $attribute
     * @return \array[][][]
     */
    public function getAttribute(array $attribute)
    {
        return [
            'objects'=>[
                'object'=>[
                    [
                        'source'=>[
                            'id'=>'ripe'
                        ],
                        'attributes'=>[
                            'attribute'=>array_merge($attribute,[

                                ['name'=>'mnt-by','value'=>env('RIPE_MNT_BY','MERCYCLOUD-MNT')],
                                ['name'=>'source','value'=>'RIPE'],
                            ])
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * 获取对象信息
     */
    public function get($objectType,$key)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
        ])->get("{$this->url}/{$objectType}/{$key}")->json();
    }

    public function create($objectType,$key,$params)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$this->url}/{$objectType}/{$key}",$params)->json();
    }

    public function delete($objectType,$key,$params)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->delete("{$this->url}/{$objectType}/{$key}",$params)->json();
    }

    /**
     * RDAP/WHOIS查询
     * @param $type
     * @param $object
     * @return array|bool|mixed
     */
    public function rdap($type,$object)
    {
        $result = Http::get("https://rdap.db.ripe.net/{$type}/{$object}");
        if ($result->ok()){
            return $result->json();
        }
        return false;
    }
}
