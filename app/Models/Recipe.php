<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class Recipe extends Model
{
    use HasFactory;

    // When protected property "$table" is not specified, the model will use the table name that is the plural of the model name, so in this instance "recipes"

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'recipe_item', 'recipe_id', 'item_id');
    }

    public function removeAllItems()
    {
        DB::table('recipe_item')->where('recipe_id', $this->id)->delete();
    }
}
