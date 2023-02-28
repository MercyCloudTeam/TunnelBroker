<?php

namespace App\Policies;

use App\Models\ASN;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ASNPolicy
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
     * @param  \App\Models\ASN  $asn
     * @return mixed
     */
    public function view(User $user, ASN $asn)
    {
        return $user->id === $asn->user_id;
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
     * @param  \App\Models\ASN  $asn
     * @return mixed
     */
    public function update(User $user, ASN $asn)
    {
        return $user->id === $asn->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ASN  $asn
     * @return mixed
     */
    public function delete(User $user, ASN $asn)
    {
        return $user->id === $asn->user_id;
    }


}
