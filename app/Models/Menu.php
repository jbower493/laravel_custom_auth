<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Recipe;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'menu_recipe', 'menu_id', 'recipe_id');
    }

    public function removeAllRecipes()
    {
        DB::table('menu_recipe')->where('menu_id', $this->id)->delete();
    }
}
