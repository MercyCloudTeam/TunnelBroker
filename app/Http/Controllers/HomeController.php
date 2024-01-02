<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $userController = new UserController();
        $trafficUsage = $userController->getTrfficUsage($user);
//        $ipAddressUsage = 0;
        $ipAddressUsage = $userController->getIpAddressUsage($user);
        $usage = array_merge($trafficUsage, $ipAddressUsage, ['tunnel' => $user->tunnels->count()]);
//        $usage = 0;
        return Inertia::render('Dashboard', [
            'my' => $user,
            'user'=>$user,
            'plan' => $user->plan,
            'userPlan' => $user->userPlan,
            'usage' => $usage
        ]);
    }

    public function lookingGlass()
    {

    }

    public function index()
    {
        $nodes = Node::all();
        return Inertia::render('Welcome', [
            'nodes' => $nodes
        ]);
    }
}
