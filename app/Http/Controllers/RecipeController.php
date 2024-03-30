<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Item;
use App\Models\QuantityUnit;
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

        $validatedRecipe = Validator::make(
            [
                'name' => $request['name'],
                'recipe_category_id' => $request['recipe_category_id'] ?? null
            ],
            [
                'name' => ['required'],
                'recipe_category_id' => ['nullable', 'integer']
            ]
        )->validate();

        $recipe = Recipe::create([
            'name' => $validatedRecipe['name'],
            'recipe_category_id' => $validatedRecipe['recipe_category_id'],
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
        $validatedRecipeData = Validator::make(
            [
                'name' => $request['name'],
                'instructions' => $request['instructions'],
                'recipe_category_id' => $request['recipe_category_id'] ?? null
            ],
            [
                'name' => ['required', 'string'],
                'instructions' => ['string', 'nullable'],
                'recipe_category_id' => ['nullable', 'integer']
            ]
        )->validate();

        // Update the model
        $recipe->name = $validatedRecipeData['name'];
        $recipe->recipe_category_id = $validatedRecipeData['recipe_category_id'];
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
        foreach ($recipe->items as $recipeItemPivot) {
            $recipeItemPivot->item_quantity->quantityUnit;
        }

        return [
            'message' => 'Recipe successfully fetched.',
            'data' => [
                'recipe' => $recipe
            ]
        ];
    }

    public function addItem(Request $request, Recipe $recipe)
    {
        $validatedNewItem = Validator::make(
            [
                'name' => $request['item_name'],
                'category_id' => $request['category_id'] ?? null,
                'quantity' => $request['quantity'],
                'quantity_unit_id' => $request['quantity_unit_id'] ?? null
            ],
            [
                'name' => ['required'],
                'category_id' => ['nullable', 'integer'],
                'quantity' => ['required'],
                'quantity_unit_id' => ['nullable', 'integer']
            ]
        )->validate();

        $recipeItemPivotAttributes = [
            'quantity' => $validatedNewItem['quantity'],
            'quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ];

        $loggedInUserId = Auth::id();

        $currentRecipeItems = $recipe->items();

        $existingItem = Item::where('name', $validatedNewItem['name'])->where('user_id', $loggedInUserId)->first();

        if ($existingItem) {
            // Check for duplicate in list
            $isDuplicate = false;

            foreach ($currentRecipeItems->get()->toArray() as &$item) {
                if ($item['name'] === $validatedNewItem['name']) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                return response([
                    'errors' => ['Item is already in this recipe. Change the quantity to add more.']
                ], 400);
            }

            $currentRecipeItems->attach($existingItem['id'], $recipeItemPivotAttributes);

            return [
                'message' => 'Item successfully added to recipe.'
            ];
        }

        // Item doesn't already exist
        $item = Item::create([
            'name' => $validatedNewItem['name'],
            'user_id' => $loggedInUserId,
            'category_id' => $validatedNewItem['category_id'],
            'default_quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ]);

        $item->save();

        $currentRecipeItems->attach($item['id'], $recipeItemPivotAttributes);

        return [
            'message' => 'Item successfully created and added to recipe.'
        ];
    }

    public function updateItemQuantity(Request $request, Recipe $recipe)
    {
        $validatedRequest = Validator::make(
            [
                'item_id' => $request['item_id'],
                'quantity' => $request['quantity'],
                'quantity_unit_id' => $request['quantity_unit_id'] ?? null
            ],
            [
                'item_id' => ['required'],
                'quantity' => ['required'],
                'quantity_unit_id' => ['nullable', 'integer']
            ]
        )->validate();

        $newQuantityUnit = QuantityUnit::findOrFail($validatedRequest['quantity_unit_id']);
        $newPivotvalues = [
            'quantity' => $validatedRequest['quantity'],
            "quantity_unit_id" => $newQuantityUnit->id
        ];

        $recipe->items()->updateExistingPivot($validatedRequest['item_id'], $newPivotvalues);

        return [
            'message' => 'Successfully updated recipe item quantity.'
        ];
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
