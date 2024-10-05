<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use App\Models\Recipe;
use App\Models\Item;
use App\Models\Menu;
use App\Models\QuantityUnit;
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
            'message' => 'List successfully created.',
            'data' => [
                'list_id' => $list->id
            ]
        ];
    }

    public function update(Request $request, ShoppingList $list)
    {
        $validatedRequest = $request->validate([
            'name' => ['required']
        ]);

        $list->name = $validatedRequest['name'];

        $list->save();

        return [
            'message' => 'List successfully updated.'
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

        $listItemPivotAttributes = [
            'quantity' => $validatedNewItem['quantity'],
            'quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ];

        $loggedInUserId = Auth::id();

        $existingItem = Item::where('name', $validatedNewItem['name'])->where('user_id', $loggedInUserId)->first();

        if ($existingItem) {
            $result = $list->addItem($existingItem['id'], $validatedNewItem['name'], $listItemPivotAttributes);

            if ($result['success']) {
                return [
                    'message' => 'Item successfully added to list.'
                ];
            }

            return response([
                'errors' => [$result['error']]
            ], 404);
        }

        // Item doesn't already exist
        $item = Item::create([
            'name' => $validatedNewItem['name'],
            'user_id' => $loggedInUserId,
            'category_id' => $validatedNewItem['category_id'],
            'default_quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ]);

        $item->save();

        $result = $list->addItem($item['id'], $item['name'], $listItemPivotAttributes);

        if (!$result['success']) {
            return response([
                'errors' => [$result['error']]
            ], 404);
        }

        return [
            'message' => 'Item successfully created and added to list.'
        ];
    }

    public function updateItemQuantity(Request $request, ShoppingList $list)
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

        $list->items()->updateExistingPivot($validatedRequest['item_id'], $newPivotvalues);

        return [
            'message' => 'Successfully updated list item quantity.'
        ];
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
            'message' => 'Items from recipe successfully added to list.'
        ];
    }

    public function addFromMenu(ShoppingList $list, Menu $menu)
    {
        // loop through all the recipes in the menu, and add them to the list
        $menuRecipes = $menu->recipes()->get()->toArray();

        foreach ($menuRecipes as $recipe) {
            $result = $list->addItemsFromRecipe($recipe['id']);

            if (!$result['success']) {
                return response([
                    'errors' => [$result['error']]
                ], 404);
            }
        }

        return [
            'message' => 'Items from menu successfully added to list.'
        ];
    }
}
