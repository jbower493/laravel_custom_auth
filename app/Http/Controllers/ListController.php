<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\Item;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $lists = ShoppingList::where('user_id', $loggedInUserId)->orderBy('created_at', 'desc')->get()->toArray();

        return [
            'message' => 'Successfully retreived lists.',
            'data' => [
                'lists' => $lists
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedList = $request->validate([
            'name' => ['required']
        ]);

        $list = ShoppingList::create([
            'name' => $validatedList['name'],
            'user_id' => $loggedInUserId
        ]);

        $list->save();

        return [
            'message' => 'List successfully created.'
        ];
    }

    public function delete($id)
    // TODO move the "clear list" into its own method on the list model
    {
        $list = ShoppingList::find($id);

        // Remove all items from list before deleting
        $list->removeAllItems();

        $list->delete();

        return [
            'message' => 'List successfully deleted.'
        ];
    }

    public function singleList($id)
    {
        $list = ShoppingList::find($id);

        if (!$list) {
            return response([
                'errors' => ['Could not find list with the requested id.']
            ], 404);
        }

        $items = $list->items()->get()->toArray();

        $list->items = $items;

        return [
            'message' => 'List successfully fetched.',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function addItem(Request $request, $id)
    {
        $list = ShoppingList::find($id);

        if (!$list) {
            return response([
                'errors' => ['Could not find list with the requested id.']
            ], 404);
        }

        $validatedNewItem = $request->validate([
            'item_name' => ['required']
        ]);

        $existingItem = Item::where('name', $validatedNewItem['item_name'])->first();

        if ($existingItem) {
            $result = $list->addItem($existingItem['id'], $existingItem['name']);

            if ($result['success']) {
                return [
                    'message' => 'Item successfully added to list.'
                ];
            }

            return response([
                'errors' => [$result['error']]
            ], 404);
        } else {
            $loggedInUserId = Auth::id();

            $item = Item::create([
                'name' => $validatedNewItem['item_name'],
                'user_id' => $loggedInUserId
            ]);
    
            $item->save();

            $result = $list->addItem($item['id'], $item['name']);

            if ($result['success']) {
                return [
                    'message' => 'Item successfully created and added to list.'
                ];
            }

            return response([
                'errors' => [$result['error']]
            ], 404);
        }
    }

    public function removeItem(Request $request, $id)
    {
        $list = ShoppingList::find($id);

        if (!$list) {
            return response([
                'errors' => ['Could not find list with the requested id.']
            ], 404);
        }

        $list->items()->detach($request->item_id);

        return [
            'message' => 'Item successfully removed from list.'
        ];
    }

    public function addFromRecipe(Request $request, $id)
    {
        $list = ShoppingList::find($id);

        if (!$list) {
            return response([
                'errors' => ['Could not find list with the requested id.']
            ], 404);
        }

        $validatedRecipe = $request->validate([
            'recipe_id' => ['required']
        ]);

        $recipe = Recipe::find($validatedRecipe['recipe_id']);

        if (!$recipe) {
            return response([
                'errors' => ['Could not find recipe with the requested id.']
            ], 404);
        }

        $recipeItems = $recipe->items()->get()->toArray();

        $someAlreadyOnList = false;

        foreach ($recipeItems as $item) {
            $result = $list->addItem($item['id'], $item['name']);

            if (!$result['success']) {
                $someAlreadyOnList = true;
            }
        }
        
        return [
            'message' => $someAlreadyOnList ? "Items from recipe successfully added to list (some we're already on the list)." : 'Items from recipe successfully added to list.'
        ]; 
    }
}
