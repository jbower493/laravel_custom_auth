<?php

namespace App\Models;

use App\Models\Pivots\MenuRecipePivot;
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

    protected $hidden = ['user_id', 'created_at', 'updated_at'];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'menu_recipe', 'menu_id', 'recipe_id')->withPivot('day')->using(MenuRecipePivot::class)->as('day_of_week');
    }

    public function removeAllRecipes()
    {
        DB::table('menu_recipe')->where('menu_id', $this->id)->delete();
    }
}
