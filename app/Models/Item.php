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
