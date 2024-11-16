<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $menus = Menu::where('user_id', $loggedInUserId)->orderBy('created_at', 'desc')->get()->toArray();

        return [
            'message' => 'Successfully retrieved menus.',
            'data' => [
                'menus' => $menus
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedMenu = $request->validate([
            'name' => ['required']
        ]);

        $menu = Menu::create([
            'name' => $validatedMenu['name'],
            'user_id' => $loggedInUserId
        ]);

        $menu->save();

        return [
            'message' => 'Menu successfully created.',
            'data' => [
                'menu_id' => $menu->id
            ]
        ];
    }

    public function update(Request $request, Menu $menu)
    {
        $validatedRequest = $request->validate([
            'name' => ['required']
        ]);

        $menu->name = $validatedRequest['name'];

        $menu->save();

        return [
            'message' => 'Menu successfully updated'
        ];
    }

    public function delete(Menu $menu)
    {
        // Remove all recipes from menu before deleting
        $menu->removeAllRecipes();

        $menu->delete();

        return [
            'message' => 'Menu successfully deleted.'
        ];
    }

    public function singleMenu(Menu $menu)
    {
        $recipes = $menu->recipes()->get()->toArray();

        $menu->recipes = $recipes;

        return [
            'message' => 'Menu successfully fetched.',
            'data' => [
                'menu' => $menu
            ]
        ];
    }

    public function addRecipe(Request $request, Menu $menu, Recipe $recipe)
    {
        $validatedRequest = Validator::make(
            [
                'day' => $request['day']
            ],
            [
                'day' => 'nullable|date_format:Y-m-d',
            ]
        )->validate();

        $date = $validatedRequest['day'] ? Carbon::createFromFormat('Y-m-d', $validatedRequest['day']) : null;

        $menu->recipes()->attach($recipe->id, ['day' => $date]);

        return [
            'message' => 'Recipe successfully added to menu.'
        ];
    }

    public function updateMenuRecipe(Request $request, Menu $menu, Recipe $recipe)
    {
        $validatedRequest = Validator::make(
            [
                'day' => $request['day']
            ],
            [
                'day' => 'nullable|date_format:Y-m-d',
            ]
        )->validate();

        $date = $validatedRequest['day'] ? Carbon::createFromFormat('Y-m-d', $validatedRequest['day']) : null;

        $menu->recipes()->updateExistingPivot($recipe['id'], ['day' => $date]);

        return [
            'message' => 'Menu recipe successfully updated.'
        ];
    }

    public function removeRecipe(Request $request, Menu $menu)
    {
        $validatedRequest = $request->validate([
            'recipe_id' => ['required', 'integer']
        ]);

        $menu->recipes()->detach($validatedRequest['recipe_id']);

        return [
            'message' => 'Recipe successfully removed from menu.'
        ];
    }

    public function randomRecipes(Request $request, Menu $menu)
    {
        $loggedInUserId = Auth::id();

        $validatedRequest = Validator::make(
            [
                'recipe_categories' => $request['recipe_categories']
            ],
            [
                'recipe_categories' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        foreach ($value as $item) {
                            $recipeId = $item['id'];
                            $quantity = $item['quantity'];

                            // Recipe id has to be EITHER "ALL_CATEGORIES" OR a number greater than 0.
                            if ($recipeId !== 'ALL_CATEGORIES' && (!is_numeric($recipeId) || $recipeId <= 0)) {
                                $fail('Recipe categories must be either one of your existing categories, or "All Categories".');
                            }

                            // If it's a number, it has to be the id of a recipe category that belongs to the user
                            if ($recipeId !== 'ALL_CATEGORIES') {
                                $loggedInUserId = Auth::id();
                                $foundRecipeCategoryBelongingToLoggedInUser = RecipeCategory::where('user_id', $loggedInUserId)->where('id', $recipeId)->get()->first();
                                if (!$foundRecipeCategoryBelongingToLoggedInUser) {
                                    $fail('Recipe categories must belong to you');
                                }
                            }

                            if (!is_numeric($quantity) || $quantity <= 0) {
                                $fail('Recipe categories must have a quantity of a number greater than 0');
                            }
                        }
                    }
                ],
            ]
        )->validate();

        // Clear all existing recipes from menu
        $menu->recipes()->detach();

        // Generate random recipes according to request and attach to menu
        $recipeCategories = $validatedRequest['recipe_categories'];
        foreach ($recipeCategories as $category) {
            $id = $category['id'];
            $quantity = $category['quantity'];

            $randomRecipesFromCategory = [];

            if ($id === 'ALL_CATEGORIES') {
                $randomRecipesFromCategory = Recipe::where('user_id', $loggedInUserId)->inRandomOrder()->take($quantity)->get();
            } else {
                $randomRecipesFromCategory = Recipe::where('recipe_category_id', $id)->inRandomOrder()->take($quantity)->get();
            }

            foreach ($randomRecipesFromCategory as $randomRecipeCategory) {
                $menu->recipes()->attach($randomRecipeCategory->id);
            }
        }

        return [
            'message' => 'Successfully generated random recipes and added them to the menu.'
        ];
    }
}
