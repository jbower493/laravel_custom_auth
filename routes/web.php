<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecipeController;

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
Route::delete('/api/item/{id}', [ItemController::class, 'delete'])->middleware('auth:web');

// Lists
Route::get('/api/list', [ListController::class, 'index'])->middleware('auth:web');
Route::post('/api/list', [ListController::class, 'store'])->middleware('auth:web');
Route::delete('/api/list/{id}', [ListController::class, 'delete'])->middleware('auth:web');
Route::get('/api/list/{id}', [ListController::class, 'singleList'])->middleware('auth:web');
Route::post('/api/list/{id}/add-item', [ListController::class, 'addItem'])->middleware('auth:web');
Route::post('/api/list/{id}/add-from-recipe', [ListController::class, 'addFromRecipe'])->middleware('auth:web');
Route::post('/api/list/{id}/remove-item', [ListController::class, 'removeItem'])->middleware('auth:web');

// Recipe
Route::get('/api/recipe', [RecipeController::class, 'index'])->middleware('auth:web');
Route::post('/api/recipe', [RecipeController::class, 'store'])->middleware('auth:web');
Route::delete('/api/recipe/{id}', [RecipeController::class, 'delete'])->middleware('auth:web');
Route::get('/api/recipe/{id}', [RecipeController::class, 'singleRecipe'])->middleware('auth:web');
Route::post('/api/recipe/{id}/add-item', [RecipeController::class, 'addItem'])->middleware('auth:web');
Route::post('/api/recipe/{id}/remove-item', [RecipeController::class, 'removeItem'])->middleware('auth:web');
