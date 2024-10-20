<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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

        $validator = Validator::make(
            [
                'name' => $request['name'],
                'category_id' => $request['category_id'] ?? null,
                'default_quantity_unit_id' => $request['default_quantity_unit_id'] ?? null
            ],
            [
                'name' => [
                    'required',
                    Rule::unique('items', 'name')->where('user_id', $loggedInUserId)
                ],
                'category_id' => ['nullable', 'integer'],
                'default_quantity_unit_id' => ['nullable', 'integer']
            ]
        );

        $validatedItem = $validator->validate();

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

        if ($validatedNewItem['default_quantity_unit_id']) {
            $item->default_quantity_unit_id = $validatedNewItem['default_quantity_unit_id'];
        }

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

    public function uploadImage(Request $request, Item $item) {
        $request->validate([
            // Size in kilobytes.
            'item_image' => 'required|file|mimes:jpg,jpeg,png|max:8192'
        ]);

        $file = $request->file('item_image');
        $mimeType = $file->getMimeType();
        $extension = explode('/', $mimeType)[1];

        $binaryFileData = $file->get();

        // Call image processing service to optimize the image and give us back optimized binary image data
        $response = Http::attach('file_to_optimize', $binaryFileData, 'file_to_optimize.' . $extension, [
            "Content-Type" => $mimeType
        ])->post(env('IMAGE_PROCESSING_SERVICE_URL') . '/optimize-image', [
            'param3' => 'value6'
        ]);

        if (!$response->successful()) {
            return response([
                'errors' => ['Failed to upload image, ' . $response->body()]
            ], 500);
        }

        // The binary data of the processed file we get back from the processing service
        $processedBinaryFileData = $response->body();

        $newFilePath = 'item-images/' . Str::random(40) . '.webp';
        $uploadSuccessful = Storage::put($newFilePath, $processedBinaryFileData);
        
        if (!$uploadSuccessful) {
            return response([
                'errors' => ['Failed to upload image.']
            ], 500);
        }

        // Once we've confirmed upload is successful, delete the old (now unused) image from storage, if one exists
        $oldImagePath = $item->short_image_url;
        if ($oldImagePath) {
            // First check if any other items are using the same image. If so, don't delete it
            $itemStillUsingImage = Item::where('image_url', $oldImagePath)->first();

            if (!$itemStillUsingImage) {
                Storage::delete($oldImagePath);
            }
        }

        $item->image_url = $newFilePath;
        $item->save();

        return [
            'message' => 'Item image successfully added.',
            'data' => [
                'url' => Storage::url($newFilePath),
            ]
        ];
    }

    public function removeImage(Item $item) {
        $currentImagePath = $item->short_image_url;

        // First check if any other items are using the same image. If so, don't delete it
        $itemStillUsingImage = Item::where('image_url', $currentImagePath)->first();

        if (!$itemStillUsingImage) {
            Storage::delete($currentImagePath);
        }

        $item->image_url = null;
        $item->save();

        return [
            'message' => 'Item image successfully removed.'
        ];
    }
}
