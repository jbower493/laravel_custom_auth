<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Item;
use App\Models\QuantityUnit;
use App\Models\RecipeShareRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $newQuantityUnit = QuantityUnit::find($validatedRequest['quantity_unit_id']);
        $newPivotvalues = [
            'quantity' => $validatedRequest['quantity'],
            "quantity_unit_id" => $newQuantityUnit ? $newQuantityUnit->id : null
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

    public function duplicate(Request $request, Recipe $recipe)
    {
        $validatedRequest = $request->validate([
            "name" => 'required'
        ]);

        $loggedInUserId = Auth::id();

        $existingRecipeWithSameName = Recipe::where('name', $validatedRequest)->where('user_id', $loggedInUserId)->first();

        if ($existingRecipeWithSameName) {
            return response([
                'errors' => ['You already have an existing recipe with this name. Please choose a different name.']
            ], 400);
        }

        $newRecipe = Recipe::create([
            'name' => $validatedRequest['name'],
            'recipe_category_id' => $recipe->recipe_category_id,
            'user_id' => $loggedInUserId
        ]);

        foreach ($recipe->items as $recipeItemPivot) {
            $quantityUnit = $recipeItemPivot->item_quantity->quantityUnit;

            $existingRecipeItemPivotAttributes = [
                'quantity' => $recipeItemPivot->item_quantity->quantity,
                'quantity_unit_id' => $quantityUnit ? $quantityUnit->id : null
            ];

            $newRecipe->items()->attach($recipeItemPivot, $existingRecipeItemPivotAttributes);
        }

        $newRecipe->save();

        return [
            'message' => 'Recipe successfully duplicated',
            'data' => [
                'new_recipe_id' => $newRecipe->id
            ]
        ];
    }

    public function createShareRequest(Request $request, Recipe $recipe)
    {
        $validatedRequest = $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $loggedInUserId = Auth::id();

        RecipeShareRequest::create([
            'owner_id' => $loggedInUserId,
            'recipient_email' => $validatedRequest['email'],
            'recipe_id' => $recipe->id
        ]);

        return [
            'message' => 'Recipe successfully shared with ' . $validatedRequest['email'],
        ];
    }

    public function acceptShareRequest(Request $request, RecipeShareRequest $recipeShareRequest)
    {
        $loggedInUserId = Auth::id();
        $loggedInUser = User::find($loggedInUserId);

        if ($recipeShareRequest->recipient_email !== $loggedInUser->email) {
            return response([
                'errors' => ['You are not authorized to access this resource.']
            ], 403);
        }

        $validatedRequest = $request->validate([
            "name" => 'required'
        ]);

        $existingRecipeWithSameName = Recipe::where('name', $validatedRequest)->where('user_id', $loggedInUserId)->first();

        if ($existingRecipeWithSameName) {
            return response([
                'errors' => ['You already have an existing recipe with this name. Please choose a different name.']
            ], 400);
        }

        $recipeToShare = Recipe::find($recipeShareRequest->recipe_id);

        $newRecipe = Recipe::create([
            'name' => $validatedRequest['name'],
            'recipe_category_id' => $recipeToShare->recipe_category_id,
            'user_id' => $loggedInUserId
        ]);

        foreach ($recipeToShare->items as $recipeItemPivot) {
            $quantityUnit = $recipeItemPivot->item_quantity->quantityUnit;

            $existingRecipeItemPivotAttributes = [
                'quantity' => $recipeItemPivot->item_quantity->quantity,
                'quantity_unit_id' => $quantityUnit ? $quantityUnit->id : null
            ];

            $newRecipe->items()->attach($recipeItemPivot, $existingRecipeItemPivotAttributes);
        }

        $newRecipe->save();

        $recipeShareRequest->delete();

        return [
            'message' => 'Recipe successfully created.',
            'data' => [
                'new_recipe_id' => $newRecipe->id
            ]
        ];
    }
}
