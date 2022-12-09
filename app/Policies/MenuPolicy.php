<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Menu;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Menu $menu)
    {
        return $user->id === $menu->user_id;
    }

    public function delete(User $user, Menu $menu)
    {
        return $user->id === $menu->user_id;
    }

    public function update(User $user, Menu $menu)
    {
        return $user->id === $menu->user_id;
    }
}
