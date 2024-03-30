<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\Pivots\ShoppingListItemPivot;
use Illuminate\Support\Facades\DB;

class ShoppingList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'name',
        'user_id'
    ];

    /**
     * The items that belong to a shopping list.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'list_item', 'list_id', 'item_id')->withPivot('quantity_unit_id', 'quantity')->using(ShoppingListItemPivot::class)->as('item_quantity');
    }

    public function removeAllItems()
    {
        DB::table('list_item')->where('list_id', $this->id)->delete();
    }

    /**
     * Take an existing item and safely add it to a list, first checking that it doesn't already exist in the list
     */
    public function addItem($itemId, $itemName)
    {
        $currentListItems = $this->items();

        // Check for duplicate in list
        $isDuplicate = false;

        foreach ($currentListItems->get()->toArray() as &$item) {
            if ($item['name'] === $itemName) {
                $isDuplicate = true;
                break;
            }
        }

        if ($isDuplicate) {
            return [
                'success' => false,
                'error' => 'Item is already in this list. Change the quantity to add more.'
            ];
        }

        $currentListItems->attach($itemId);

        return [
            'success' => true
        ];
    }

    public function addItemsFromRecipe($recipeId)
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return [
                'success' => false,
                'error' => 'Could not find recipe with requested id.'
            ];
        }

        $recipeItems = $recipe->items()->get()->toArray();

        $someAlreadyOnList = false;

        foreach ($recipeItems as $item) {
            $result = $this->addItem($item['id'], $item['name']);

            if (!$result['success']) {
                $someAlreadyOnList = true;
            }
        }

        return [
            'success' => true,
            'some_already_on_list' => $someAlreadyOnList
        ];
    }
}
