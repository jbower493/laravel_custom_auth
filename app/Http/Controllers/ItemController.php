<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
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
                'category_id' => $request['category_id'] ?? null,
                'default_quantity_unit_id' => $request['default_quantity_unit_id'] ?? null
            ],
            [
                'name' => ['required'],
                'category_id' => ['nullable', 'integer'],
                'default_quantity_unit_id' => ['nullable', 'integer']
            ]
        )->validate();

        $item = Item::create([
            'name' => $validatedItem['name'],
            'category_id' => $validatedItem['category_id'],
            'user_id' => $loggedInUserId,
            'default_quantity_unit_id' => $validatedItem['default_quantity_unit_id']
        ]);

        $item->save();

        return [
            'message' => 'Item successfully created.'
        ];
    }

    public function update(Request $request, Item $item)
    {
        $validatedNewItem = Validator::make(
            [
                'name' => $request['name'],
                'category_id' => $request['category_id'] ?? null,
                'default_quantity_unit_id' => $request['default_quantity_unit_id'] ?? null
            ],
            [
                'name' => ['required'],
                'category_id' => ['nullable', 'integer'],
                'default_quantity_unit_id' => ['nullable', 'integer']
            ]
        )->validate();

        $item->name = $validatedNewItem['name'];
        $item->category_id = $validatedNewItem['category_id'];
        $item->default_quantity_unit_id = $validatedNewItem['default_quantity_unit_id'];

        $item->save();

        return [
            'message' => 'Item successfully updated.'
        ];
    }

    public function delete(Item $item)
    {
        // Remove from all lists and recipes before deleting
        $item->removeFromAllLists();
        $item->removeFromAllRecipes();

        $item->delete();

        return [
            'message' => 'Item successfully deleted.'
        ];
    }

    public function bulkAssignCategory(Request $request, $categoryId)
    {
        $categoryIdToSet = null;

        $loggedInUserId = Auth::id();

        // If category id is -1, set the items to uncategorized
        if ($categoryId != -1) {
            $category = Category::find($categoryId);

            // If no category with that id
            if (!$category) {
                return response([
                    'errors' => ["No category with this id exists."]
                ], 404);
            }

            // If the category doesnt belong to the user
            if ($category->user_id !== $loggedInUserId) {
                return response([
                    'errors' => ["You are not authorized to access this resource."]
                ], 403);
            }

            $categoryIdToSet = $category->id;
        }

        $validatedRequest = $request->validate([
            'item_ids.*' => ['required', 'integer'],
        ]);

        // Validate that all items are owned by the user        
        $items = Item::whereIn('id', $validatedRequest['item_ids'])->get()->toArray();

        foreach ($items as $item) {
            if ($item['user_id'] !== $loggedInUserId) {
                return response([
                    'errors' => ["You are not authorized to access this resource."]
                ], 403);
            }
        }

        Item::whereIn('id', $validatedRequest['item_ids'])->update([
            'category_id' => $categoryIdToSet
        ]);

        return [
            'message' => 'Items successfully assigned to category.'
        ];
    }
}
