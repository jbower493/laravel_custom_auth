<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Menu;
use App\Repositories\AuthedUserRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Menu $menu)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $menu->user_id;
    }

    public function delete(?User $user, Menu $menu)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $menu->user_id;
    }

    public function update(?User $user, Menu $menu)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $menu->user_id;
    }
}
