<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Recipe;

class MenuController extends Controller
{
    public function index()
    {
        $loggedInUserId = Auth::id();

        $menus = Menu::where('user_id', $loggedInUserId)->orderBy('created_at', 'desc')->get()->toArray();

        return [
            'message' => 'Successfully retrieved menus.',
            'data' => [
                'menus' => $menus
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = Auth::id();

        $validatedMenu = $request->validate([
            'name' => ['required']
        ]);

        $menu = Menu::create([
            'name' => $validatedMenu['name'],
            'user_id' => $loggedInUserId
        ]);

        $menu->save();

        return [
            'message' => 'Menu successfully created.'
        ];
    }

    public function delete(Menu $menu)
    {
        // Remove all recipes from menu before deleting
        $menu->removeAllRecipes();

        $menu->delete();

        return [
            'message' => 'Menu successfully deleted.'
        ];
    }

    public function singleMenu(Menu $menu)
    {
        $recipes = $menu->recipes()->get()->toArray();

        $menu->recipes = $recipes;

        return [
            'message' => 'Menu successfully fetched.',
            'data' => [
                'menu' => $menu
            ]
        ];
    }

    public function addRecipe(Menu $menu, Recipe $recipe)
    {
        $menu->recipes()->attach($recipe->id);

        return [
            'message' => 'Recipe successfully added to menu.'
        ];
    }

    public function removeRecipe(Request $request, Menu $menu)
    {
        $validatedRequest = $request->validate([
            'recipe_id' => ['required', 'integer']
        ]);

        $menu->recipes()->detach($validatedRequest['recipe_id']);

        return [
            'message' => 'Recipe successfully removed from menu.'
        ];
    }
}
