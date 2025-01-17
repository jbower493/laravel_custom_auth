<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ShoppingList;
use App\Repositories\AuthedUserRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShoppingListPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, ShoppingList $shoppingList)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $shoppingList->user_id;
    }

    public function delete(?User $user, ShoppingList $shoppingList)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $shoppingList->user_id;
    }

    public function update(?User $user, ShoppingList $shoppingList)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $shoppingList->user_id;
    }
}
