<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RecipeCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecipeCategoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, RecipeCategory $recipeCategory)
    {
        return $user->id === $recipeCategory->user_id;
    }

    public function delete(User $user, RecipeCategory $recipeCategory)
    {
        return $user->id === $recipeCategory->user_id;
    }

    public function update(User $user, RecipeCategory $recipeCategory)
    {
        return $user->id === $recipeCategory->user_id;
    }
}
