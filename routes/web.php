<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuantityUnitsController;
use App\Http\Controllers\RecipeCategoryController;

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
Route::get('/api/user', [AuthController::class, 'getUser'])->middleware('checkAuthed:web');
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);
Route::get('/api/logout', [AuthController::class, 'logout'])->middleware('checkAuthed:web');
Route::post('/api/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/api/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// SSO
Route::get('/auth/google/redirect', [App\Http\Controllers\GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [App\Http\Controllers\GoogleLoginController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// TODO: all these routes are accessible by someone logged into the account as an additional user. Make a policy to prevent that.
// Account
Route::get('/api/user/additional-user', [AccountController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/user/additional-user', [AccountController::class, 'store'])->middleware('checkAuthed:web');
Route::post('/api/user/additional-user/remove', [AccountController::class, 'remove'])->middleware('checkAuthed:web');
Route::get('/api/user/additional-user/account-access', [AccountController::class, 'accountAccess'])->middleware('checkAuthed:web');
Route::post('/api/user/additional-user/login-as-another-user', [AccountController::class, 'loginAsAnotherUser'])->middleware('checkAuthed:web');

Route::post('/api/user/change-email', [AccountController::class, 'changeEmail'])->middleware('checkAuthed:web');
Route::post('/api/user/change-password', [AccountController::class, 'changePassword'])->middleware('checkAuthed:web');
Route::delete('/api/user/{user}', [AccountController::class, 'deleteAccount'])->middleware('checkAuthed:web');

Route::get('/api/user/notifications', [AccountController::class, 'notifications'])->middleware('checkAuthed:web');

// Items
Route::get('/api/item', [ItemController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/item', [ItemController::class, 'store'])->middleware('checkAuthed:web');
Route::put('/api/item/{item}', [ItemController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,item');
Route::delete('/api/item/{item}', [ItemController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,item');
Route::put('/api/item/category/{categoryId}/bulk', [ItemController::class, 'bulkAssignCategory'])->middleware('checkAuthed:web');
Route::post('/api/item/{item}/upload-image', [ItemController::class, 'uploadImage'])->middleware('checkAuthed:web')->middleware('can:update,item');
Route::delete('/api/item/{item}/remove-image', [ItemController::class, 'removeImage'])->middleware('checkAuthed:web')->middleware('can:update,item');

// Lists
Route::get('/api/list', [ListController::class, 'index'])->middleware('checkAuthed:web');
// Route::get('/api/list', [ListController::class, 'index']);
Route::post('/api/list', [ListController::class, 'store'])->middleware('checkAuthed:web');
Route::put('/api/list/{list}', [ListController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,list');
Route::delete('/api/list/{list}', [ListController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,list');
Route::get('/api/list/{list}', [ListController::class, 'singleList'])->middleware('checkAuthed:web')->middleware('can:view,list');
Route::post('/api/list/{list}/add-item', [ListController::class, 'addItem'])->middleware('checkAuthed:web')->middleware('can:update,list');
Route::put('/api/list/{list}/update-item-quantity', [ListController::class, 'updateItemQuantity'])->middleware('checkAuthed:web')->middleware('can:update,list');
Route::post('/api/list/{list}/remove-item', [ListController::class, 'removeItem'])->middleware('checkAuthed:web')->middleware('can:update,list');
Route::post('/api/list/{list}/add-from-recipe/{recipe}', [ListController::class, 'addFromRecipe'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,list')
    ->middleware('can:update,recipe');
Route::post('/api/list/{list}/add-from-menu/{menu}', [ListController::class, 'addFromMenu'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,list')
    ->middleware('can:update,menu');

// Recipe
Route::get('/api/recipe', [RecipeController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/recipe', [RecipeController::class, 'store'])->middleware('checkAuthed:web');
Route::post('/api/recipe/import-from-image', [RecipeController::class, 'importFromImage'])->middleware('checkAuthed:web');
Route::delete('/api/recipe/{recipe}', [RecipeController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,recipe');
Route::get('/api/recipe/{recipe}', [RecipeController::class, 'singleRecipe'])->middleware('checkAuthed:web')->middleware('can:view,recipe');
Route::put('/api/recipe/{recipe}', [RecipeController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,recipe');
Route::post('/api/recipe/{recipe}/add-item', [RecipeController::class, 'addItem'])->middleware('checkAuthed:web')->middleware('can:update,recipe');
Route::put('/api/recipe/{recipe}/update-item-quantity', [RecipeController::class, 'updateItemQuantity'])->middleware('checkAuthed:web')->middleware('can:update,recipe');
Route::post('/api/recipe/{recipe}/remove-item', [RecipeController::class, 'removeItem'])->middleware('checkAuthed:web')->middleware('can:update,recipe');
Route::post('/api/recipe/{recipe}/duplicate', [RecipeController::class, 'duplicate'])->middleware('checkAuthed:web')->middleware('can:view,recipe');
Route::post('/api/recipe/{recipe}/create-share-request', [RecipeController::class, 'createShareRequest'])->middleware('checkAuthed:web')->middleware('can:view,recipe');
Route::post('/api/recipe/accept-share-request/{recipeShareRequest}', [RecipeController::class, 'acceptShareRequest'])->middleware('checkAuthed:web');
Route::post('/api/recipe/{recipe}/upload-image', [RecipeController::class, 'uploadImage'])->middleware('checkAuthed:web')->middleware('can:update,recipe');
Route::delete('/api/recipe/{recipe}/remove-image', [RecipeController::class, 'removeImage'])->middleware('checkAuthed:web')->middleware('can:update,recipe');

Route::post('/api/recipe/import-from-chrome-extension', [RecipeController::class, 'importFromChromeExtension']);
Route::post('/api/recipe/confirm-import-from-chrome-extension/{importedRecipe}', [RecipeController::class, 'confirmImportFromChromeExtension'])->middleware('checkAuthed:web')->middleware('can:delete,importedRecipe');


// Menu
Route::get('/api/menu', [MenuController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/menu', [MenuController::class, 'store'])->middleware('checkAuthed:web');
Route::put('/api/menu/{menu}', [MenuController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,menu');
Route::delete('/api/menu/{menu}', [MenuController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,menu');
Route::get('/api/menu/{menu}', [MenuController::class, 'singleMenu'])->middleware('checkAuthed:web')->middleware('can:view,menu');
Route::post('/api/menu/{menu}/add-recipes', [MenuController::class, 'addRecipes'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,menu');
Route::put('/api/menu/{menu}/update-menu-recipe/{recipe}', [MenuController::class, 'updateMenuRecipe'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,menu')
    ->middleware('can:update,recipe');
Route::post('/api/menu/{menu}/remove-recipe', [MenuController::class, 'removeRecipe'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,menu');
Route::put('/api/menu/{menu}/random-recipes/preview', [MenuController::class, 'randomRecipesPreview'])
    ->middleware('checkAuthed:web')
    ->middleware('can:update,menu');

// Categories
Route::get('/api/category', [CategoryController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/category', [CategoryController::class, 'store'])->middleware('checkAuthed:web');
Route::put('/api/category/{category}', [CategoryController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,category');
Route::delete('/api/category/{category}', [CategoryController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,category');

// Recipe categories
Route::get('/api/recipe-category', [RecipeCategoryController::class, 'index'])->middleware('checkAuthed:web');
Route::post('/api/recipe-category', [RecipeCategoryController::class, 'store'])->middleware('checkAuthed:web');
Route::put('/api/recipe-category/{recipeCategory}', [RecipeCategoryController::class, 'update'])->middleware('checkAuthed:web')->middleware('can:update,recipeCategory');
Route::delete('/api/recipe-category/{recipeCategory}', [RecipeCategoryController::class, 'delete'])->middleware('checkAuthed:web')->middleware('can:delete,recipeCategory');

// Quantity units
Route::get('/api/quantity-unit', [QuantityUnitsController::class, 'index'])->middleware('checkAuthed:web');
