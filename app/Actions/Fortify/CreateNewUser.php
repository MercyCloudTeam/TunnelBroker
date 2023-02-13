<?php

namespace App\Actions\Fortify;

use App\Models\Plan;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        DB::beginTransaction();
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
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
            'reset_day' => now()->day,
        ]);

        DB::commit();

        return $user;
    }

    /**
     * @throws Exception
     */
    public function userPlan()
    {
        $defaultPlanId = env('DEFAULT_PLAN_ID');
        if (!isset($defaultPlanId)) {
            throw new Exception('Please set DEFAULT_PLAN_ID in .env file');
        }
        $defaultPlan = Plan::where('id', $defaultPlanId)->first();
        if (empty($defaultPlan) && $defaultPlanId == 1) {
            //Initial Default Plan
            $defaultPlan = Plan::create([
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Free Plan',
                'limit' => env('INITIAL_PLAN_LIMIT', 5),
                'ipv4_num' => env('INITIAL_PLAN_IPV4', 5),
                'ipv6_num' => env('INITIAL_PLAN_IPV6', 5),
                'traffic' => env('INITIAL_PLAN_SPEED', 107374182400),//bytes
                'speed' => env('INITIAL_PLAN_SPEED', 1000),//bytes
            ]);
        } elseif(empty($defaultPlan)) {
            throw new Exception('DEFAULT_PLAN_ID plan not found');
        }
        return $defaultPlan;
    }
}
