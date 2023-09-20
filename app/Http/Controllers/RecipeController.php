<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\RecipeUtils\FromUrl;

class RecipeController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $recipes = Recipe::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retrieved recipes.',
            'data' => [
                'recipes' => $recipes
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedRecipe = $request->validate([
            'name' => ['required']
        ]);

        $recipe = Recipe::create([
            'name' => $validatedRecipe['name'],
            'user_id' => $loggedInUserId
        ]);

        $recipe->save();

        return [
            'message' => 'Recipe successfully created.'
        ];
    }

    public function update(Request $request, Recipe $recipe)
    {
        // Validate the recipe data
        $validatedRecipeData = $request->validate([
            'name' => ['required', 'string'],
            'instructions' => ['string', 'nullable']
        ]);

        // Update the model
        $recipe->name = $validatedRecipeData['name'];
        $recipe->instructions = $validatedRecipeData['instructions'];

        // Save the model
        $recipe->save();

        // Send response
        return [
            'message' => 'Recipe successfully updated.'
        ];
    }

    public function delete(Recipe $recipe)
    {
        // Remove from all menus that it belongs to
        $recipe->removeFromAllMenus();

        // Remove all items from recipe before deleting
        $recipe->removeAllItems();

        $recipe->delete();

        return [
            'message' => 'Recipe successfully deleted.'
        ];
    }

    public function singleRecipe(Recipe $recipe)
    {
        $items = $recipe->items()->get()->toArray();

        $recipe->items = $items;

        return [
            'message' => 'Recipe successfully fetched.',
            'data' => [
                'recipe' => $recipe
            ]
        ];
    }

    public function addItem(Request $request, Recipe $recipe)
    {
        $validatedItem = $request->validate([
            'item_name' => ['required']
        ]);

        $loggedInUserId = Auth::id();

        $currentRecipeItems = $recipe->items();

        $existingItem = Item::where('name', $validatedItem['item_name'])->where('user_id', $loggedInUserId)->first();

        if ($existingItem) {
            // Check for duplicate in list
            $isDuplicate = false;

            foreach ($currentRecipeItems->get()->toArray() as &$item) {
                if ($item['name'] === $validatedItem['item_name']) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                return response([
                    'errors' => ['Item is already in this recipe. Change the quantity to add more.']
                ], 400);
            }

            $currentRecipeItems->attach($existingItem['id']);

            return [
                'message' => 'Item successfully added to recipe.'
            ];
        } else {
            $validatedNewItem = Validator::make(
                [
                    'name' => $validatedItem['item_name'],
                    'category_id' => $request['category_id'] ?? null
                ],
                [
                    'name' => ['required'],
                    'category_id' => ['nullable', 'integer']
                ]
            )->validate();

            $item = Item::create([
                'name' => $validatedItem['item_name'],
                'user_id' => $loggedInUserId,
                'category_id' => $validatedNewItem['category_id']
            ]);

            $item->save();

            $currentRecipeItems->attach($item['id']);

            return [
                'message' => 'Item successfully created and added to recipe.'
            ];
        }
    }

    public function removeItem(Request $request, Recipe $recipe)
    {
        $recipe->items()->detach($request->item_id);

        return [
            'message' => 'Item successfully removed from recipe.'
        ];
    }

    public function fromUrl(Request $request, Recipe $recipe)
    {
        $fromUrl = new FromUrl($request['url']);

        $attemptInstructions = $fromUrl->getInstructions();

        if (!$attemptInstructions['success']) {
            return response([
                'errors' => ['Could not get instructions from the provided url.']
            ], 400);
        }

        $recipe->instructions = $attemptInstructions['instructions'];
        $recipe->save();

        return [
            'message' => 'Recipe successfully imported from url.'
        ];
    }
}
