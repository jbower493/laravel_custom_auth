<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $recipes = Recipe::where('user_id', $loggedInUserId)->get()->toArray();

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

    public function delete($id)
    {
        $recipe = Recipe::find($id);

        // Remove all items from recipe before deleting
        DB::table('list_item')->where('list_id', $recipe['id'])->delete();

        $recipe->delete();

        return [
            'message' => 'Recipe successfully deleted.'
        ];
    }

    public function singleRecipe($id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response([
                'errors' => ['Could not find recipe with the requested id.']
            ], 404);
        }

        $items = $recipe->items()->get()->toArray();

        $recipe->items = $items;

        return [
            'message' => 'Recipe successfully fetched.',
            'data' => [
                'recipe' => $recipe
            ]
        ];
    }

    public function addItem(Request $request, $id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response([
                'errors' => ['Could not find recipe with the requested id.']
            ], 404);
        }

        $validatedNewItem = $request->validate([
            'item_name' => ['required']
        ]);

        $existingItem = Item::where('name', $validatedNewItem['item_name'])->first();

        if ($existingItem) {
            $recipe->items()->attach($existingItem['id']);

            return [
                'message' => 'Item successfully added to recipe.'
            ];
        } else {
            $loggedInUserId = Auth::id();

            $item = Item::create([
                'name' => $validatedNewItem['item_name'],
                'user_id' => $loggedInUserId
            ]);
    
            $item->save();

            $recipe->items()->attach($item['id']);

            return [
                'message' => 'Item successfully created and added to recipe.'
            ];
        }
    }

    public function removeItem(Request $request, $id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response([
                'errors' => ['Could not find recipe with the requested id.']
            ], 404);
        }

        $recipe->items()->detach($request->item_id);

        return [
            'message' => 'Item successfully removed from recipe.'
        ];
    }
}
