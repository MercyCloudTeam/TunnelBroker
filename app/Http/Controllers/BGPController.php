<?php

namespace App\Http\Controllers;

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
        $tunnels = Tunnel::where('user_id',$user->id)->join('nodes_components',function ($join){
            $join->on('tunnels.node_id','=','nodes_components.node_id')
                ->where('nodes_components.component','=','FRR')
                ->where('nodes_components.status','=','active');
        })->get();
        //Get Hava Nodes Components FRR Node
//        $nodes = Node::active()->whereHas('components', function ($query) {
//            $query->where('component', 'FRR')->where('status','active');
//        })->get();
        return Inertia::render('BGP/Index',[
            'asn'=>$user->asn,
            'tunnels'=>$tunnels
        ]);
    }
}
