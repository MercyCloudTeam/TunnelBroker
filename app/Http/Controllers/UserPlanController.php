<?php

namespace App\Http\Controllers;

use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPlanController extends Controller
{

    public function edit()
    {

    }

    public function get(Request $request)
    {
        $this->validate($request,[
            'user'=>'exists:users,id'
        ]);
        $user = User::find($request->user);
        return new JsonResource($user->plan);
    }

    public function delete()
    {

    }
}
