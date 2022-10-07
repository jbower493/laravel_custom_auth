<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $items = Item::where('user_id', $loggedInUserId)->get()->toArray();

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

        $validatedItem = $request->validate([
            'name' => ['required']
        ]);

        $item = Item::create([
            'name' => $validatedItem['name'],
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

        $item->delete();

        return [
            'message' => 'Item successfully deleted.'
        ];
    }
}
