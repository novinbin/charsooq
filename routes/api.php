<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/users/manager.php';
require __DIR__ . '/users/employee.php';
require __DIR__ . '/users/author.php';
require __DIR__ . '/users/user.php';
require __DIR__ . '/users/organ.php';

//---------------------------- User -----------------------------
Route::middleware('auth:sanctum')->get('/self', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->post('/self', [UserController::class, 'updateSelf']);

//---------------------------- Blogs ----------------------------
Route::get('/blogs', [BlogController::class, 'getAll']);
Route::get('/blog/{blog:slug}', [BlogController::class, 'show']);
