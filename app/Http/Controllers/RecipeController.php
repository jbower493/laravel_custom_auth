<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;

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

        $recipe->items()->attach($request->item_id);

        return [
            'message' => 'Item successfully added to recipe.'
        ];
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
