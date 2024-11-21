<?php

use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'isAuthor'])->prefix('author/')->group(function () {

    //---------------------------- Blogs ----------------------------
    Route::get('blogs', [BlogController::class, 'getAll']);
    Route::get('blog/{blog}', [BlogController::class, 'show']);
    Route::post('blog', [BlogController::class, 'create']);
    Route::post('blog/{blog}', [BlogController::class, 'update']);
    Route::delete('blog/{blog}', [BlogController::class, 'destroy']);

});
