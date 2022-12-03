<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $items = Item::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retreived items.',
            'data' => [
                'items' => $items
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedItem = Validator::make(
            [
                'name' => $request['name'],
                'category_id' => $request['category_id'] ?? null
            ],
            [
                'name' => ['required'],
                'category_id' => ['nullable', 'integer']
            ]
        )->validate();

        $item = Item::create([
            'name' => $validatedItem['name'],
            'category_id' => $validatedItem['category_id'],
            'user_id' => $loggedInUserId
        ]);

        $item->save();

        return [
            'message' => 'Item successfully created.'
        ];
    }

    public function delete($id)
    {
        $item = Item::find($id);

        // Remove from all lists and recipes before deleting
        $item->removeFromAllLists();
        $item->removeFromAllRecipes();

        $item->delete();

        return [
            'message' => 'Item successfully deleted.'
        ];
    }

    public function assignToCategory($itemId, Request $request)
    {
        $item = Item::find($itemId);

        $validatedRequest = $request->validate([
            'category_id' => ['required', 'integer']
        ]);

        $result = $item->assignToCategory($validatedRequest['category_id']);

        if (!$result['success']) {
            return response([
                'errors' => [$result['error']]
            ], 404);
        }

        return [
            'message' => 'Item successfully assigned to category.'
        ];
    }
}
