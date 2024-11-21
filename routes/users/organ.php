<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'isOrgan'])->prefix('organ/')->group(function () {

    //---------------------------- Unknown ----------------------------


});
