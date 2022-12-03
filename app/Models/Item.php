<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'user_id'
    ];

    // Eager load the item's category by default
    protected $with = ['category'];

    // Omits the "category_id" from any collection of Items that is retrieved
    protected $hidden = ['category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function removeFromAllLists()
    {
        DB::table('list_item')->where('item_id', $this->id)->delete();
    }

    public function removeFromAllRecipes()
    {
        DB::table('recipe_item')->where('item_id', $this->id)->delete();
    }

    public function assignToCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return [
                'success' => false,
                'error' => 'Category with that id doesn\'t exist.'
            ];
        }

        $this->category_id = $categoryId;
        $this->save();

        return [
            'success' => true
        ];
    }
}
