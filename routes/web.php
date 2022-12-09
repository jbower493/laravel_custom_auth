<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/api/get-csrf', [AuthController::class, 'getCsrfToken']);

// Auth
Route::get('/api/user', [AuthController::class, 'getUser']);
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);
Route::get('/api/logout', [AuthController::class, 'logout'])->middleware('auth:web');

// Items
Route::get('/api/item', [ItemController::class, 'index'])->middleware('auth:web');
Route::post('/api/item', [ItemController::class, 'store'])->middleware('auth:web');
Route::delete('/api/item/{item}', [ItemController::class, 'delete'])->middleware('auth:web')->middleware('can:delete,item');
Route::put('/api/item/category/{categoryId}/bulk', [ItemController::class, 'bulkAssignCategory'])->middleware('auth:web');

// Lists
Route::get('/api/list', [ListController::class, 'index'])->middleware('auth:web');
Route::post('/api/list', [ListController::class, 'store'])->middleware('auth:web');
Route::delete('/api/list/{list}', [ListController::class, 'delete'])->middleware('auth:web')->middleware('can:delete,list');
Route::get('/api/list/{list}', [ListController::class, 'singleList'])->middleware('auth:web')->middleware('can:view,list');
Route::post('/api/list/{list}/add-item', [ListController::class, 'addItem'])->middleware('auth:web')->middleware('can:update,list');
Route::post('/api/list/{list}/remove-item', [ListController::class, 'removeItem'])->middleware('auth:web')->middleware('can:update,list');
Route::post('/api/list/{list}/add-from-recipe/{recipe}', [ListController::class, 'addFromRecipe'])
    ->middleware('auth:web')
    ->middleware('can:update,list')
    ->middleware('can:update,recipe');
Route::post('/api/list/{list}/add-from-menu/{menu}', [ListController::class, 'addFromMenu'])
    ->middleware('auth:web')
    ->middleware('can:update,list')
    ->middleware('can:update,menu');

// Recipe
Route::get('/api/recipe', [RecipeController::class, 'index'])->middleware('auth:web');
Route::post('/api/recipe', [RecipeController::class, 'store'])->middleware('auth:web');
// TODO: authorize
Route::delete('/api/recipe/{id}', [RecipeController::class, 'delete'])->middleware('auth:web');
// TODO: authorize
Route::get('/api/recipe/{id}', [RecipeController::class, 'singleRecipe'])->middleware('auth:web');
// TODO: authorize
Route::post('/api/recipe/{id}/add-item', [RecipeController::class, 'addItem'])->middleware('auth:web');
// TODO: authorize
Route::post('/api/recipe/{id}/remove-item', [RecipeController::class, 'removeItem'])->middleware('auth:web');

// Menu
Route::get('/api/menu', [MenuController::class, 'index'])->middleware('auth:web');
Route::post('/api/menu', [MenuController::class, 'store'])->middleware('auth:web');
// TODO: authorize
Route::delete('/api/menu/{id}', [MenuController::class, 'delete'])->middleware('auth:web');
// TODO: authorize
Route::get('/api/menu/{id}', [MenuController::class, 'singleMenu'])->middleware('auth:web');
// TODO: authorize
Route::post('/api/menu/{id}/add-recipe', [MenuController::class, 'addRecipe'])->middleware('auth:web');
// TODO: authorize
Route::post('/api/menu/{id}/remove-recipe', [MenuController::class, 'removeRecipe'])->middleware('auth:web');

// Categories
Route::get('/api/category', [CategoryController::class, 'index'])->middleware('auth:web');
Route::post('/api/category', [CategoryController::class, 'store'])->middleware('auth:web');
// TODO: authorize
Route::delete('/api/category/{id}', [CategoryController::class, 'delete'])->middleware('auth:web');
