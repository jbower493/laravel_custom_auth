<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\RecipeCategory;
use App\Models\Recipe;
use App\Repositories\AuthedUserRepositoryInterface;
use Illuminate\Http\Request;

class RecipeCategoryController extends Controller
{
    protected $authedUserRepo;

    public function __construct(AuthedUserRepositoryInterface $authedUserRepo)
    {
        $this->authedUserRepo = $authedUserRepo;
    }

    public function index()
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

        $recipeCategories = RecipeCategory::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retreived recipe categories.',
            'data' => [
                'recipe_categories' => $recipeCategories
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

        $validatedRecipeCategory = $request->validate([
            'name' => ['required']
        ]);

        $recipeCategory = RecipeCategory::create([
            'name' => $validatedRecipeCategory['name'],
            'user_id' => $loggedInUserId
        ]);

        $recipeCategory->save();

        return [
            'message' => 'Recipe category successfully created.'
        ];
    }

    public function update(Request $request, RecipeCategory $recipeCategory)
    {
        $validatedRequest = $request->validate([
            'name' => ['required']
        ]);

        $recipeCategory->name = $validatedRequest['name'];

        $recipeCategory->save();

        return [
            'message' => 'Recipe category successfully updated'
        ];
    }

    public function delete(RecipeCategory $recipeCategory)
    {
        // set all Recipe with that recipe category to have a recipe category of null, before deleting the recipe category
        Recipe::where('recipe_category_id', $recipeCategory->id)->update(['recipe_category_id' => null]);

        $recipeCategory->delete();

        return [
            'message' => 'Recipe category successfully deleted.'
        ];
    }
}
