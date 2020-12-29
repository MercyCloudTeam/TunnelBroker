<?php

namespace App\Http\Controllers;

use App\Http\Requests\TunnelRequest;
use App\Http\Resources\TunnelResource;
use App\Http\Resources\TunnelsCollectionResource;
use App\Jobs\ChangeTunnelIP;
use App\Jobs\DeleteTunnel;
use App\Models\IPAllocation;
use App\Models\Node;
use App\Models\Tunnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use IPTools\Network;

class TunnelAPIController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/tunnels",
     *     @OA\Response(response="200", description="An example resource")
     * )
     */


    /**
     *
     * Display a listing of the resource.
     *
     * @return TunnelsCollectionResource
     */
    public function index()
    {
        return new TunnelsCollectionResource(Auth::user()->tunnels());
    }

    /**
     * API DDNS更新Tunnel IP
     * @param Tunnel $tunnel
     * @param Request $request
     */
    public function tunnelDDNSUpdate(Tunnel $tunnel,Request $request)
    {
        if ($tunnel !== $request->ip()){//如果请求IP与当前记录不同
            //数据记录更新
            $tunnel->update([
                'status'=>4
            ]);
            ChangeTunnelIP::dispatch($tunnel);//更改Tunnel对端ip
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TunnelRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(TunnelRequest $request)
    {
        $status = (new TunnelController())->storeAction($request);
        if (is_string($status)){
            return $this->jsonResult($status);
        }else{
            return $this->jsonResult('SUCCESS', $status->toArray());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Tunnel $tunnel
     * @return TunnelResource
     */
    public function show(Tunnel $tunnel)
    {
        return new TunnelResource($tunnel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Tunnel $tunnel)
    {
        $status = (new TunnelController())->updateAction($request,$tunnel);;
        if ($status) {
            return $this->jsonResult('SUCCESS');
        }
        return $this->jsonResult("ERROR");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Tunnel $tunnel
     * @return void
     * @throws \Exception
     */
    public function destroy(Tunnel $tunnel)
    {
        $this->authorize('delete', $tunnel);
        DeleteTunnel::dispatch($tunnel);
        IPAllocation::where('tunnel_id',$tunnel->id)->update(['tunnel_id'=>null]);//IP重新进入分配表
        $tunnel->delete();
    }

}
