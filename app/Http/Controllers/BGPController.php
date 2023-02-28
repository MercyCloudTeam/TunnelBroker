<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBGPRequest;
use App\Models\ASN;
use App\Models\BGPSession;
use App\Models\Node;
use App\Models\Tunnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BGPController extends Controller
{

    /**
     * 页面返回
     * @return \Inertia\Response
     */
    public function index()
    {
        $user = Auth::user();
        $tunnels = Tunnel::where('user_id', $user->id)->join('nodes_components', function ($join) {
            $join->on('tunnels.node_id', '=', 'nodes_components.node_id')
                ->where('nodes_components.component', '=', 'FRR')
                ->where('nodes_components.status', '=', 'active');
        })->select('*', 'tunnels.id as tunnel_id')->get();
        //Get Hava Nodes Components FRR Node
//        $nodes = Node::active()->whereHas('components', function ($query) {
//            $query->where('component', 'FRR')->where('status','active');
//        })->get();
        $bgp = BGPSession::where('user_id', $user->id)->with(['asn', 'tunnel'])->get();
        return Inertia::render('BGP/Index', [
            'asn' => $user->asn->where('validate', true),
            'tunnels' => $tunnels,
            'bgp' => $bgp,
        ]);
    }

    public function store(CreateBGPRequest $request)
    {
        $asn = ASN::find($request->asn);
        BGPSession::create([
            'asn_id' => $request->asn,
            'tunnel_id' => $request->tunnel,
            'status' => 2,
            'user_id' => Auth::user()->id,
            'limit' => $asn->limit,
        ]);
//        return redirect()->route('bgp.index');
    }

    public function rebuild(BGPSession $bgp)
    {
        $this->authorize('update', $bgp);
        $bgp->status = 3;
        $bgp->save();
        return redirect()->route('bgp.index');
    }

    public function destroy(BGPSession $bgp)
    {
        $this->authorize('delete', $bgp);
        $bgp->status = 4;
        $bgp->save();
        return redirect()->route('bgp.index');
    }
}
