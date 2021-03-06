<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\Auth;

class ListController extends Controller
{
    public function index(Request $request)
    {
        $loggedInUserId = Auth::id();

        $lists = ShoppingList::all()->toArray();

        return $lists;
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $list = new ShoppingList;

        $list->name = $request->name;
        $list->user_id = $loggedInUserId;

        $list->save();

        return [
            'message' => 'List successfully created.'
        ];
    }
}
