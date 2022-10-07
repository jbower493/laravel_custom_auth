<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $lists = ShoppingList::where('user_id', $loggedInUserId)->get()->toArray();

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
    {
        $list = ShoppingList::find($id);

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

        $list->items()->attach($request->item_id);

        return [
            'message' => 'Item successfully added to list.'
        ];
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
}
