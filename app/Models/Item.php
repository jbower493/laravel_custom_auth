<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
}
