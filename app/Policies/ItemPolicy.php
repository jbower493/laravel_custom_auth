<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Item;
use App\Repositories\AuthedUserRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Item $item)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $item->user_id;
    }

    public function delete(?User $user, Item $item)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $item->user_id;
    }

    public function update(?User $user, Item $item)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $item->user_id;
    }
}
