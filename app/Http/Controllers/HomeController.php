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
        return Inertia::render('Dashboard',[
            'my'=>$user,
            'plan'=>$user->plan,
        ]);
    }

    public function index()
    {
        $nodes = Node::all();
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'nodes'=> $nodes
        ]);
    }
}
