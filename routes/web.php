<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;

// use App\Mail\Welcome;
// use Illuminate\Support\Facades\Mail;

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
Route::post('/api/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/api/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

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
Route::delete('/api/recipe/{recipe}', [RecipeController::class, 'delete'])->middleware('auth:web')->middleware('can:delete,recipe');
Route::get('/api/recipe/{recipe}', [RecipeController::class, 'singleRecipe'])->middleware('auth:web')->middleware('can:view,recipe');
Route::put('/api/recipe/{recipe}', [RecipeController::class, 'update'])->middleware('auth:web')->middleware('can:update,recipe');
Route::post('/api/recipe/{recipe}/add-item', [RecipeController::class, 'addItem'])->middleware('auth:web')->middleware('can:update,recipe');
Route::post('/api/recipe/{recipe}/remove-item', [RecipeController::class, 'removeItem'])->middleware('auth:web')->middleware('can:update,recipe');

// Menu
Route::get('/api/menu', [MenuController::class, 'index'])->middleware('auth:web');
Route::post('/api/menu', [MenuController::class, 'store'])->middleware('auth:web');
Route::delete('/api/menu/{menu}', [MenuController::class, 'delete'])->middleware('auth:web')->middleware('can:delete,menu');
Route::get('/api/menu/{menu}', [MenuController::class, 'singleMenu'])->middleware('auth:web')->middleware('can:view,menu');
Route::post('/api/menu/{menu}/add-recipe/{recipe}', [MenuController::class, 'addRecipe'])
    ->middleware('auth:web')
    ->middleware('can:update,menu')
    ->middleware('can:update,recipe');
Route::post('/api/menu/{menu}/remove-recipe', [MenuController::class, 'removeRecipe'])
    ->middleware('auth:web')
    ->middleware('can:update,menu');

// Categories
Route::get('/api/category', [CategoryController::class, 'index'])->middleware('auth:web');
Route::post('/api/category', [CategoryController::class, 'store'])->middleware('auth:web');
Route::delete('/api/category/{category}', [CategoryController::class, 'delete'])->middleware('auth:web')->middleware('can:delete,category');

// Example of how to send an email
// Route::get('/api/email', function() {
//     Mail::to(Auth::user())->send(new Welcome());

//     return 'Email sent';
// })->middleware('auth:web');
