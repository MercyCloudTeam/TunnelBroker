<?php

namespace App\Http\Controllers;

use App\Models\IPAllocation;
use App\Models\TunnelTraffic;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function getIpAddressUsage(User $user)
    {
        $ipAllocation = $user->ipAllocation;
        //Does not count intranet IPs
//        $ipAllocation = $ipAllocation->where('intranet', false);
        $ipv4Usage = $ipAllocation->where('type', 'ipv4')->count();
        $ipv6Usage = $ipAllocation->where('type', 'ipv6')->count();
        return [
            'ipv4' => $ipv4Usage,
            'ipv6' => $ipv6Usage
        ];
    }

    public function apiCreateUser(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => 'nullable',
            'email_verified'=>'nullable|boolean'
        ]);
        DB::beginTransaction();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? Str::random()),
            'email_verified_at'=>($request->email_verified == true ? Carbon::now() : null)
        ]);

        try {
            $plan = $this->userPlan();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $user->userPlan()->create([
            'plan_id' => $plan->id,
            'expire_at' => now()->addYears(10),
            'reset_day'=> now()->day,
        ]);

        DB::commit();

        event(new Registered($user));
    }


    public function getTrfficUsage(User $user)
    {
        $tunnelsTraffics = TunnelTraffic::where([
            ['user_id', $user->id],
            ['deadline', '>', now()],
        ])->get();
//        $tunnels = $user->tunnels->pluck('id');
        $in = 0;
        $out = 0;
        $total = 0;
        foreach ($tunnelsTraffics as $traffic)
        {
            if (!empty($traffic)){
                $in += $traffic->in;
                $out += $traffic->out;
                $total += $traffic->in + $traffic->out;
            }
        }
        return [
            'in' => $in,
            'out' => $out,
            'total' => $total,
        ];
    }

}
