<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;

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

// Lists
Route::get('/api/list', [ListController::class, 'index'])->middleware('auth:web');
Route::post('/api/list', [ListController::class, 'store'])->middleware('auth:web');
Route::delete('/api/list/{id}', [ListController::class, 'delete'])->middleware('auth:web');
