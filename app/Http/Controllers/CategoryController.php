<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $categories = Category::where('user_id', $loggedInUserId)->orderBy('created_at', 'desc')->get()->toArray();

        return [
            'message' => 'Successfully retreived categories.',
            'data' => [
                'categories' => $categories
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedCategory = $request->validate([
            'name' => ['required']
        ]);

        $category = Category::create([
            'name' => $validatedCategory['name'],
            'user_id' => $loggedInUserId
        ]);

        $category->save();

        return [
            'message' => 'Category successfully created.'
        ];
    }

    public function delete(Category $category)
    {
        // set all items with that category to have a category of null, before deleting the category
        Item::where('category_id', $category->id)->update(['category_id' => null]);

        $category->delete();

        return [
            'message' => 'Category successfully deleted.'
        ];
    }
}
