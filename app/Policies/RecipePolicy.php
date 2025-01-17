<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Recipe;
use App\Repositories\AuthedUserRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecipePolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Recipe $recipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $recipe->user_id;
    }

    public function delete(?User $user, Recipe $recipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $recipe->user_id;
    }

    public function update(?User $user, Recipe $recipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $recipe->user_id;
    }
}
