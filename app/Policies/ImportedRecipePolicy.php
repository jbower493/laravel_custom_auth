<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImportedRecipe;
use App\Repositories\AuthedUserRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImportedRecipePolicy
{
    use HandlesAuthorization;

    public function view(?User $user, ImportedRecipe $importedRecipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $importedRecipe->user_id;
    }

    public function delete(?User $user, ImportedRecipe $importedRecipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $importedRecipe->user_id;
    }

    public function update(?User $user, ImportedRecipe $importedRecipe)
    {
        $authedUserRepo = new AuthedUserRepository();
        $authedUser = $authedUserRepo->getUser();

        if (!$authedUser) {
            return false;
        }

        return $authedUser->id === $importedRecipe->user_id;
    }
}
