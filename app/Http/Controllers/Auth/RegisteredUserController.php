<?php

namespace App\Http\Controllers\Auth;

use App\Models\Plan;
use DB;
use Exception;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::beginTransaction();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * @throws Exception
     */
    public function userPlan()
    {
        $defaultPlanId = env('DEFAULT_PLAN_ID');
        if (empty($defaultPlanId)) {
            throw new Exception('Please set DEFAULT_PLAN_ID in .env file');
        }
        $defaultPlan = Plan::where('id', $defaultPlanId)->first();
        if (empty($defaultPlan) && $defaultPlanId == 1) {
            //Initial Default Plan
            $defaultPlan = Plan::create([
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Free Plan',
                'ipv4_num' => env('INITIAL_PLAN_IPV4', 5),
                'ipv6_num' => env('INITIAL_PLAN_IPV6', 5),
                'limit' => env('INITIAL_PLAN_TRAFFIC', 1024000),
                'traffic' => 1000,
            ]);
        }
        return $defaultPlan;
    }
}
