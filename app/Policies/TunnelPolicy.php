<?php

namespace App\Policies;

use App\Models\Tunnel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TunnelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tunnel  $tunnel
     * @return mixed
     */
    public function view(User $user, Tunnel $tunnel)
    {
        return $user->id === $tunnel->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tunnel  $tunnel
     * @return mixed
     */
    public function update(User $user, Tunnel $tunnel)
    {
        return $user->id === $tunnel->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Tunnel  $tunnel
     * @return mixed
     */
    public function delete(User $user, Tunnel $tunnel)
    {
        return $user->id === $tunnel->user_id;
    }


}
