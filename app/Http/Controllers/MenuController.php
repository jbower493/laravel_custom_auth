<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Repositories\AuthedUserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    protected $authedUserRepo;

    public function __construct(AuthedUserRepositoryInterface $authedUserRepo)
    {
        $this->authedUserRepo = $authedUserRepo;
    }

    public function index()
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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

    public function addRecipes(Request $request, Menu $menu)
    {
        $validatedRequest = Validator::make(
            [
                'recipes' => $request['recipes']
            ],
            [
                'recipes' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        $loggedInUserId = $this->authedUserRepo->getUser()->id;

                        foreach ($value as $singleRecipe) {
                            $recipeId = $singleRecipe['id'];
                            $day = $singleRecipe['day'];

                            Validator::make(['day' => $day], ['day' => 'nullable|date_format:Y-m-d'])->validate();

                            $foundRecipe = Recipe::where('user_id', $loggedInUserId)->where('id', $recipeId)->get()->first();

                            if (!$foundRecipe) {
                                $fail('All recipes to add to a menu must belong to the menu owner.');
                            }
                        }
                    }
                ],
            ]
        )->validate();

        foreach ($validatedRequest['recipes'] as $recipeToAdd) {
            $date = $recipeToAdd['day'] ? Carbon::createFromFormat('Y-m-d', $recipeToAdd['day']) : null;

            $menu->recipes()->attach($recipeToAdd['id'], ['day' => $date]);
        }

        $pluralized = count($validatedRequest['recipes']) === 1 ? 'Recipe' : 'Recipes';

        return [
            'message' => $pluralized . ' successfully added to menu.'
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

    public function randomRecipesPreview(Request $request, Menu $menu)
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

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
                                $loggedInUserId = $this->authedUserRepo->getUser()->id;
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

        // Generate random recipes according to request
        $recipes = [];

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

            foreach ($randomRecipesFromCategory as $randomRecipe) {
                array_push($recipes, $randomRecipe);
            }
        }

        return [
            'message' => 'Successfully generated random recipes and added them to the menu.',
            'data' => [
                "recipes" => $recipes
            ]
        ];
    }
}
