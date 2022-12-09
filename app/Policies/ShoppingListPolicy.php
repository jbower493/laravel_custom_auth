<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ShoppingList;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShoppingListPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ShoppingList $shoppingList)
    {
        return $user->id === $shoppingList->user_id;
    }

    public function delete(User $user, ShoppingList $shoppingList)
    {
        return $user->id === $shoppingList->user_id;
    }

    public function update(User $user, ShoppingList $shoppingList)
    {
        return $user->id === $shoppingList->user_id;
    }
}
