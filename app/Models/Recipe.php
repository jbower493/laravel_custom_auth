<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use App\Models\RecipeCategory;
use Illuminate\Support\Facades\DB;

class Recipe extends Model
{
    use HasFactory;

    // When protected property "$table" is not specified, the model will use the table name that is the plural of the model name, so in this instance "recipes"

    protected $fillable = [
        'name',
        'user_id',
        'instructions',
        'recipe_category_id'
    ];

    // Eager load the recipe's recipe category by default
    protected $with = ['recipeCategory'];

    // Omits the "recipe_category_id" from any collection of recipes that is retrieved
    protected $hidden = ['recipe_category_id'];

    // This method has to be named the same as the "protected $with" name above, or we will get "call to undefined relationship"
    public function recipeCategory()
    {
        return $this->belongsTo(RecipeCategory::class);
    }

    public function items()
    {
        // $items = $this->belongsToMany(Item::class, 'recipe_item', 'recipe_id', 'item_id')->withPivot('quantity', 'quantity_unit_id');

        $items = $this->belongsToMany(Item::class, 'recipe_item', 'recipe_id', 'item_id')->withPivot('quantity', 'quantity_unit_id');

        // foreach ($items as $item) {
        //     $quantityUnit = $item->pivot->quantityUnit;
        //     dump($quantityUnit);
        // }

        // dd($items->get()->toArray());

        // ORIGINAL
        // $items = $this->belongsToMany(Item::class, 'recipe_item', 'recipe_id', 'item_id');

        return $items;
    }

    public function removeFromAllMenus()
    {
        DB::table('menu_recipe')->where('recipe_id', $this->id)->delete();
    }

    public function removeAllItems()
    {
        DB::table('recipe_item')->where('recipe_id', $this->id)->delete();
    }

    public function assignToRecipeCategory($recipeCategoryId)
    {
        $recipeCategory = RecipeCategory::find($recipeCategoryId);

        if (!$recipeCategory) {
            return [
                'success' => false,
                'error' => 'Recipe category with that id doesn\'t exist.'
            ];
        }

        $this->recipe_category_id = $recipeCategory;
        $this->save();

        return [
            'success' => true
        ];
    }
}
