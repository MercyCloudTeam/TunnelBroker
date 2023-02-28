<?php

namespace App\Policies;

use App\Models\BGPSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BGPSessionPolicy
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
     * @param  \App\Models\BGPSession  $session
     * @return mixed
     */
    public function view(User $user, BGPSession $session)
    {
        return $user->id === $session->user_id;
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
     * @param  \App\Models\BGPSession  $session
     * @return mixed
     */
    public function update(User $user, BGPSession $session)
    {
        return $user->id === $session->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\BGPSession  $session
     * @return mixed
     */
    public function delete(User $user, BGPSession $session)
    {
        return $user->id === $session->user_id;
    }


}
