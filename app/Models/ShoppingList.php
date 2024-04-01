<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\Pivots\ShoppingListItemPivot;
use App\Utils\Converter;
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
    public function addItem($itemId, $itemName, $listItemPivotAttributes = [])
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

        $currentListItems->attach($itemId, $listItemPivotAttributes);

        return [
            'success' => true
        ];
    }

    public function addItemsFromRecipe($recipeId)
    // TODO:
    // 1. Get it just adding quantities, ignoring units
    // 2. Make it convert units to the first of every clash it encounters and then add values
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return [
                'success' => false,
                'error' => 'Could not find recipe with requested id.'
            ];
        }

        // $recipeItems = $recipe->items()->get()->toArray();

        // foreach ($recipe->items as $recipeItemPivot) {
        //     $recipeItemPivot->item_quantity->quantityUnit;
        // }

        foreach ($recipe->items as $recipeItemPivot) {
            // Check for duplicate in list
            $alreadyExistingItem = DB::table('list_item')->where('list_id', $this->id)->where('item_id', $recipeItemPivot->id)->first();

            if ($alreadyExistingItem) {
                // Convert to same unit and add up quantities

                $originalListQuantityValue = $alreadyExistingItem->quantity;
                $originalListQuantityUnit = QuantityUnit::find($alreadyExistingItem->quantity_unit_id);
                $originalListQuantityUnitName = $originalListQuantityUnit ? $originalListQuantityUnit->name : null;

                $recipeQuantityValue = $recipeItemPivot->item_quantity->quantity;
                $recipeQuantityUnit = $recipeItemPivot->item_quantity->quantityUnit;
                $recipeQuantityUnitName = $recipeQuantityUnit ? $recipeQuantityUnit->name : null;

                $converter = new Converter();
                $convertedRecipeQuantityValue = $converter->convert($recipeQuantityValue, $recipeQuantityUnitName, $originalListQuantityUnitName);

                if ($convertedRecipeQuantityValue === 0) {
                    // TODO: handle if it failed to convert, eg. incompatible units. Ideally we would abort the whole thing
                }

                // At the moment just always use the existing list quantity unit
                $newQuantityValue = $originalListQuantityValue + $convertedRecipeQuantityValue;
                $newQuantityUnitId = $alreadyExistingItem->quantity_unit_id;

                $updatedListItemPivotAttributes = [
                    'quantity' => $newQuantityValue,
                    'quantity_unit_id' => $newQuantityUnitId
                ];

                $this->items()->updateExistingPivot($alreadyExistingItem->item_id, $updatedListItemPivotAttributes);
            } else {
                // Attach to list, with same quantity and unit as recipe
                $recipeQuantityUnit = $recipeItemPivot->item_quantity->quantityUnit;

                $listItemPivotAttributes = [
                    'quantity' => $recipeItemPivot->item_quantity->quantity,
                    'quantity_unit_id' => $recipeQuantityUnit ? $recipeQuantityUnit->id : null
                ];

                $this->items()->attach($recipeItemPivot->id, $listItemPivotAttributes);
            }
        }

        return [
            'success' => true
        ];
    }
}
