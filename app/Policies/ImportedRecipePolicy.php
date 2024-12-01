<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ImportedRecipe;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImportedRecipePolicy
{
    use HandlesAuthorization;

    public function view(User $user, ImportedRecipe $importedRecipe)
    {
        return $user->id === $importedRecipe->user_id;
    }

    public function delete(User $user, ImportedRecipe $importedRecipe)
    {
        return $user->id === $importedRecipe->user_id;
    }

    public function update(User $user, ImportedRecipe $importedRecipe)
    {
        return $user->id === $importedRecipe->user_id;
    }
}
