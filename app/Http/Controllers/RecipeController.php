<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Item;
use App\Models\QuantityUnit;
use App\Models\RecipeShareRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\SharedRecipe;
use App\Models\ImportedRecipe;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RecipeController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $recipes = Recipe::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retrieved recipes.',
            'data' => [
                'recipes' => $recipes
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedRecipe = Validator::make(
            [
                'name' => $request['name'],
                'instructions' => $request['instructions'],
                'recipe_category_id' => $request['recipe_category_id'] ?? null,
                'prep_time' => $request['prep_time'],
                'serves' => $request['serves']
            ],
            [
                'name' => ['required'],
                'instructions' => ['string', 'nullable'],
                'recipe_category_id' => ['nullable', 'integer'],
                'prep_time' => ['nullable', 'integer'],
                'serves' => ['nullable', 'integer']
            ]
        )->validate();

        $recipe = Recipe::create([
            'name' => $validatedRecipe['name'],
            'recipe_category_id' => $validatedRecipe['recipe_category_id'],
            'instructions' => $validatedRecipe['instructions'] ?? '',
            'user_id' => $loggedInUserId,
            'prep_time' => $validatedRecipe['prep_time'],
            'serves' => $validatedRecipe['serves']
        ]);

        $recipe->save();

        return [
            'message' => 'Recipe successfully created.',
            'data' => [
                'recipe_id' => $recipe->id
            ]
        ];
    }

    public function importFromImage(Request $request)
    {
        $request->validate([
            // Size in kilobytes.
            'import_from_image' => 'required|file|mimes:jpg,jpeg,png|max:8192'
        ]);

        $file = $request->file('import_from_image');
        $mimeType = $file->getMimeType();
        $extension = explode('/', $mimeType)[1];

        $binaryFileData = $file->get();

        // Call image processing service to optimize the image and give us back optimized binary image data
        $response = Http::attach('image_to_text', $binaryFileData, 'image_to_text.' . $extension, [
            "Content-Type" => $mimeType
        ])->post(env('IMAGE_PROCESSING_SERVICE_URL') . '/image-to-text', [
            'param3' => 'value6'
        ]);

        if (!$response->successful()) {
            return response([
                'errors' => ['Failed to import recipe from image']
            ], 500);
        }

        $data = $response->json();
        $textRegions = $data['text_regions'];

        return [
            'message' => 'Recipe successfully imported from image.',
            'data' => [
                'imported_recipe' => $textRegions,
            ]
        ];
    }

    public function update(Request $request, Recipe $recipe)
    {
        // Validate the recipe data
        $validatedRecipeData = Validator::make(
            [
                'name' => $request['name'],
                'instructions' => $request['instructions'],
                'recipe_category_id' => $request['recipe_category_id'] ?? null,
                'prep_time' => $request['prep_time'],
                'serves' => $request['serves']
            ],
            [
                'name' => ['required', 'string'],
                'instructions' => ['string', 'nullable'],
                'recipe_category_id' => ['nullable', 'integer'],
                'prep_time' => ['nullable', 'integer'],
                'serves' => ['nullable', 'integer']
            ]
        )->validate();

        // Update the model
        $recipe->name = $validatedRecipeData['name'];
        $recipe->recipe_category_id = $validatedRecipeData['recipe_category_id'];
        $recipe->instructions = $validatedRecipeData['instructions'];
        $recipe->prep_time = $validatedRecipeData['prep_time'];
        $recipe->serves = $validatedRecipeData['serves'];

        // Save the model
        $recipe->save();

        // Send response
        return [
            'message' => 'Recipe successfully updated.'
        ];
    }

    public function delete(Recipe $recipe)
    {
        // Remove from all menus that it belongs to
        $recipe->removeFromAllMenus();

        // Remove all items from recipe before deleting
        $recipe->removeAllItems();

        $recipe->delete();

        return [
            'message' => 'Recipe successfully deleted.'
        ];
    }

    public function singleRecipe(Recipe $recipe)
    {
        foreach ($recipe->items as $recipeItemPivot) {
            $recipeItemPivot->item_quantity->quantityUnit;
        }

        return [
            'message' => 'Recipe successfully fetched.',
            'data' => [
                'recipe' => $recipe
            ]
        ];
    }

    public function addItem(Request $request, Recipe $recipe)
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

        $recipeItemPivotAttributes = [
            'quantity' => $validatedNewItem['quantity'],
            'quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ];

        $loggedInUserId = Auth::id();

        $currentRecipeItems = $recipe->items();

        $existingItem = Item::where('name', $validatedNewItem['name'])->where('user_id', $loggedInUserId)->first();

        if ($existingItem) {
            // Check for duplicate in list
            $isDuplicate = false;

            foreach ($currentRecipeItems->get()->toArray() as &$item) {
                if ($item['name'] === $validatedNewItem['name']) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                return response([
                    'errors' => ['Item is already in this recipe. Change the quantity to add more.']
                ], 400);
            }

            $currentRecipeItems->attach($existingItem['id'], $recipeItemPivotAttributes);

            return [
                'message' => 'Item successfully added to recipe.'
            ];
        }

        // Item doesn't already exist
        $item = Item::create([
            'name' => $validatedNewItem['name'],
            'user_id' => $loggedInUserId,
            'category_id' => $validatedNewItem['category_id'],
            'default_quantity_unit_id' => $validatedNewItem['quantity_unit_id']
        ]);

        $item->save();

        $currentRecipeItems->attach($item['id'], $recipeItemPivotAttributes);

        return [
            'message' => 'Item successfully created and added to recipe.'
        ];
    }

    public function updateItemQuantity(Request $request, Recipe $recipe)
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

        $recipe->items()->updateExistingPivot($validatedRequest['item_id'], $newPivotvalues);

        return [
            'message' => 'Successfully updated recipe item quantity.'
        ];
    }

    public function removeItem(Request $request, Recipe $recipe)
    {
        $recipe->items()->detach($request->item_id);

        return [
            'message' => 'Item successfully removed from recipe.'
        ];
    }

    public function duplicate(Request $request, Recipe $recipe)
    {
        $validatedRequest = $request->validate([
            "name" => 'required'
        ]);

        $loggedInUserId = Auth::id();

        $existingRecipeWithSameName = Recipe::where('name', $validatedRequest)->where('user_id', $loggedInUserId)->first();

        if ($existingRecipeWithSameName) {
            return response([
                'errors' => ['You already have an existing recipe with this name. Please choose a different name.']
            ], 400);
        }

        $newRecipe = Recipe::create([
            'name' => $validatedRequest['name'],
            'recipe_category_id' => $recipe->recipe_category_id,
            'user_id' => $loggedInUserId,
            'instructions' => $recipe->instructions
        ]);

        foreach ($recipe->items as $recipeItemPivot) {
            $quantityUnit = $recipeItemPivot->item_quantity->quantityUnit;

            $existingRecipeItemPivotAttributes = [
                'quantity' => $recipeItemPivot->item_quantity->quantity,
                'quantity_unit_id' => $quantityUnit ? $quantityUnit->id : null
            ];

            $newRecipe->items()->attach($recipeItemPivot, $existingRecipeItemPivotAttributes);
        }

        $newRecipe->save();

        return [
            'message' => 'Recipe successfully duplicated',
            'data' => [
                'new_recipe_id' => $newRecipe->id
            ]
        ];
    }

    public function createShareRequest(Request $request, Recipe $recipe)
    {
        $validatedRequest = $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $loggedInUserId = Auth::id();
        $loggedInUser = User::find($loggedInUserId);

        $newShareRequest = RecipeShareRequest::create([
            'owner_id' => $loggedInUserId,
            'recipient_email' => $validatedRequest['email'],
            'recipe_id' => $recipe->id
        ]);

        $isExistingUserWithRecipientEmail = !!User::where('email', $validatedRequest['email'])->first();

        Mail::to($validatedRequest['email'])->send(new SharedRecipe($loggedInUser->name, $validatedRequest['email'], $isExistingUserWithRecipientEmail, $newShareRequest->id, $recipe));

        return [
            'message' => 'Recipe successfully shared with ' . $validatedRequest['email'] . '. An email has been sent to the recipient to notify them that the recipe has been shared with them.',
        ];
    }

    public function acceptShareRequest(Request $request, RecipeShareRequest $recipeShareRequest)
    {
        $loggedInUserId = Auth::id();
        $loggedInUser = User::find($loggedInUserId);

        if ($recipeShareRequest->recipient_email !== $loggedInUser->email) {
            return response([
                'errors' => ['You are not authorized to access this resource.']
            ], 403);
        }

        $validatedRequest = $request->validate([
            "name" => 'required'
        ]);

        $existingRecipeWithSameName = Recipe::where('name', $validatedRequest)->where('user_id', $loggedInUserId)->first();

        if ($existingRecipeWithSameName) {
            return response([
                'errors' => ['You already have an existing recipe with this name. Please choose a different name.']
            ], 400);
        }

        $recipeToShare = Recipe::find($recipeShareRequest->recipe_id);

        $newRecipe = Recipe::create([
            'name' => $validatedRequest['name'],
            'recipe_category_id' => null,
            'user_id' => $loggedInUserId,
            'instructions' => $recipeToShare->instructions,
            'image_url' => $recipeToShare->short_image_url
        ]);

        foreach ($recipeToShare->items as $recipeItemPivot) {
            $quantityUnit = $recipeItemPivot->item_quantity->quantityUnit;

            $existingRecipeItemPivotAttributes = [
                'quantity' => $recipeItemPivot->item_quantity->quantity,
                'quantity_unit_id' => $quantityUnit ? $quantityUnit->id : null
            ];

            // If the user accepting the share request already has an item with that name, use the existing item. If not, create a new item with no category
            $item = Item::where('name', $recipeItemPivot->name)->where('user_id', $loggedInUserId)->first();

            if (!$item) {
                $item = Item::create([
                    'name' => $recipeItemPivot->name,
                    'category_id' => null,
                    'user_id' => $loggedInUserId,
                    'default_quantity_unit_id' => $recipeItemPivot->defautl_quantity_unit_id
                ]);
            }

            $newRecipe->items()->attach($item->id, $existingRecipeItemPivotAttributes);
        }

        $newRecipe->save();

        $recipeShareRequest->delete();

        return [
            'message' => 'Recipe successfully created.',
            'data' => [
                'new_recipe_id' => $newRecipe->id
            ]
        ];
    }

    public function uploadImage(Request $request, Recipe $recipe)
    {
        $request->validate([
            // Size in kilobytes.
            'recipe_image' => 'required|file|mimes:jpg,jpeg,png|max:8192'
        ]);

        $file = $request->file('recipe_image');
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

        $newFilePath = 'recipe-images/' . Str::random(40) . '.webp';
        $uploadSuccessful = Storage::put($newFilePath, $processedBinaryFileData);

        if (!$uploadSuccessful) {
            return response([
                'errors' => ['Failed to upload image.']
            ], 500);
        }

        // Once we've confirmed upload is successful, delete the old (now unused) image from storage, if one exists
        $oldImagePath = $recipe->short_image_url;
        if ($oldImagePath) {
            // First check if any other recipes are using the same image (eg. from shared recipes). If so, don't delete it
            $recipeStillUsingImage = Recipe::where('image_url', $oldImagePath)->first();

            if (!$recipeStillUsingImage) {
                Storage::delete($oldImagePath);
            }
        }

        $recipe->image_url = $newFilePath;
        $recipe->save();

        return [
            'message' => 'Recipe image successfully added.',
            'data' => [
                'url' => Storage::url($newFilePath),
            ]
        ];
    }

    public function removeImage(Recipe $recipe)
    {
        $currentImagePath = $recipe->short_image_url;

        // First check if any other recipes are using the same image (eg. from shared recipes). If so, don't delete it
        $recipeStillUsingImage = Recipe::where('image_url', $currentImagePath)->first();

        if (!$recipeStillUsingImage) {
            Storage::delete($currentImagePath);
        }

        $recipe->image_url = null;
        $recipe->save();

        return [
            'message' => 'Recipe image successfully removed.'
        ];
    }

    public function importFromChromeExtension(Request $request)
    {
        $user = User::where('email', $request['email'])->first();

        if (!$user) {
            return response([
                'errors' => ['User with that email does not exist.']
            ], 400);
        }

        $validatedRequest = Validator::make(
            [
                'name' => $request['recipe']['name'],
                'instructions' => $request['recipe']['instructions'],
                'recipe_category' => $request['recipe']['recipe_category'] ?? null,
                'prep_time' => $request['recipe']['prep_time'],
                'serves' => $request['recipe']['serves'],
                'items' => $request['recipe']['items']
            ],
            [
                'name' => ['required'],
                'instructions' => ['string', 'nullable'],
                'recipe_category' => ['nullable', 'integer'],
                'prep_time' => ['nullable', 'integer'],
                'serves' => ['nullable', 'integer'],
                'items' => ['required', 'array'],
                'items.*.name' => ['required', 'string'],
                'items.*.quantity' => ['required', 'numeric'],
                'items.*.quantity_unit' => ['nullable', 'string']
            ]
        )->validate();

        ImportedRecipe::create([
            'user_id' => $user->id,
            'data' => json_encode($validatedRequest)
        ]);

        return [
            'message' => 'Recipe successfully imported.'
        ];
    }

    public function confirmImportFromChromeExtension(Request $request, ImportedRecipe $importedRecipe)
    {
        $loggedInUserId = Auth::id();

        $validatedRequest = Validator::make(
            [
                'name' => $request['name'],
                'instructions' => $request['instructions'],
                'recipe_category_id' => $request['recipe_category_id'] ?? null,
                'prep_time' => $request['prep_time'],
                'serves' => $request['serves'],
                'items' => $request['items']
            ],
            // Same as new recipe validator, except this one includes items list
            [
                'name' => ['required'],
                'instructions' => ['string', 'nullable'],
                'recipe_category_id' => ['nullable', 'integer'],
                'prep_time' => ['nullable', 'integer'],
                'serves' => ['nullable', 'integer'],
                'items' => ['required', 'array'],
                'items.*.name' => ['required', 'string'],
                'items.*.quantity' => ['required', 'numeric'],
                'items.*.quantity_unit_id' => ['nullable', 'integer'],
                'items.*.category_id' => ['nullable', 'integer']
            ]
        )->validate();

        $recipe = Recipe::create([
            'name' => $validatedRequest['name'],
            'recipe_category_id' => $validatedRequest['recipe_category_id'],
            'instructions' => $validatedRequest['instructions'] ?? '',
            'user_id' => $loggedInUserId,
            'prep_time' => $validatedRequest['prep_time'],
            'serves' => $validatedRequest['serves']
        ]);

        foreach ($validatedRequest['items'] as $itemToAdd) {
            $name = $itemToAdd['name'];
            $quantity = $itemToAdd['quantity'];
            $quantityUnitId = $itemToAdd['quantity_unit_id'];

            $existingItem = Item::where('name', $name)->where('user_id', $loggedInUserId)->first();

            $pivotvalues = [
                'quantity' => $quantity,
                "quantity_unit_id" => $quantityUnitId
            ];

            if ($existingItem) {
                $recipe->items()->attach($existingItem->id, $pivotvalues);
                continue;
            }

            // Item doesn't already exist
            $item = Item::create([
                'name' => $name,
                'user_id' => $loggedInUserId,
                'category_id' => $itemToAdd['category_id'],
                'default_quantity_unit_id' => $quantityUnitId
            ]);

            $item->save();

            $recipe->items()->attach($item['id'], $pivotvalues);
        }

        $recipe->save();

        $importedRecipe->delete();

        return [
            'message' => 'Recipe import successfully confirmed.',
            'data' => [
                'new_recipe_id' => $recipe->id
            ]
        ];
    }
}
