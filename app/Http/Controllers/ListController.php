<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\Recipe;
use App\Models\Item;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function delete(ShoppingList $list)
    {
        // Remove all items from list before deleting
        $list->removeAllItems();

        $list->delete();

        return [
            'message' => 'List successfully deleted.'
        ];
    }

    public function singleList(ShoppingList $list)
    {
        foreach ($list->items as $listItemPivot) {
            $listItemPivot->item_quantity->quantityUnit;
        }

        return [
            'message' => 'List successfully fetched.',
            'data' => [
                'list' => $list
            ]
        ];
    }

    public function addItem(Request $request, ShoppingList $list)
    {
        $validatedItem = $request->validate([
            'item_name' => ['required']
        ]);

        $loggedInUserId = Auth::id();

        $existingItem = Item::where('name', $validatedItem['item_name'])->where('user_id', $loggedInUserId)->first();

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
                'name' => $validatedNewItem['name'],
                'user_id' => $loggedInUserId,
                'category_id' => $validatedNewItem['category_id']
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

    public function removeItem(Request $request, ShoppingList $list)
    {
        $list->items()->detach($request->item_id);

        return [
            'message' => 'Item successfully removed from list.'
        ];
    }

    public function addFromRecipe(ShoppingList $list, Recipe $recipe)
    {
        $result = $list->addItemsFromRecipe($recipe->id);

        if (!$result['success']) {
            return response([
                'errors' => [$result['error']]
            ], 404);
        }

        return [
            'message' => $result['some_already_on_list'] ? "Items from recipe successfully added to list (some we're already on the list)." : 'Items from recipe successfully added to list.'
        ];
    }

    public function addFromMenu(ShoppingList $list, Menu $menu)
    {
        // loop through all the recipes in the menu, and add them to the list
        $menuRecipes = $menu->recipes()->get()->toArray();

        $someAlreadyOnList = false;

        foreach ($menuRecipes as $recipe) {
            $result = $list->addItemsFromRecipe($recipe['id']);

            if ($result['some_already_on_list']) {
                $someAlreadyOnList = true;
            }

            if (!$result['success']) {
                return response([
                    'errors' => [$result['error']]
                ], 404);
            }
        }

        return [
            'message' => $someAlreadyOnList ? "Items from menu successfully added to list (some we're already on the list)." : 'Items from menu successfully added to list.'
        ];
    }
}
