<?php

use App\Http\Controllers\BalanceIncreaseController;
use App\Http\Controllers\CatFirstController;
use App\Http\Controllers\CatSecondController;
use App\Http\Controllers\CatThirdController;
use App\Http\Controllers\DashtController;
use App\Http\Controllers\FactorController;
use App\Http\Controllers\FactorItemController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\OrganController;
use App\Http\Controllers\UserCategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'isEmployee'])->prefix('employee/')->group(function () {

    //---------------------------- Dasht ----------------------------
        Route::get('dasht/products', [DashtController::class, 'getProducts']);
        Route::get('dasht/invoices', [DashtController::class, 'getInvoices']);

    //------------------------- User Control ------------------------
        Route::get('users', [UserController::class, 'getAllCustomers']);
        Route::get('user/{user}', [UserController::class, 'read']);
        Route::post('user', [UserController::class, 'create']);
        Route::put('user', [UserController::class, 'update']);
        Route::delete('user', [UserController::class, 'delete']);
        Route::get('category/{category}/users', [UserController::class, 'getUsersByCategory']);
        Route::get('organ/{organ}/users', [UserController::class, 'getUsersByCategory']);

    //----------------------- Balance Increase ----------------------
        Route::get('user/{user}/balance-increase', [BalanceIncreaseController::class, 'getUserRequestList']);
        Route::get('balance-increase/{balance}', [BalanceIncreaseController::class, 'getBalance']);
        Route::post('user/{user}/balance-increase', [BalanceIncreaseController::class, 'create']);
        Route::post('balance-increase/{balance}', [BalanceIncreaseController::class, 'update']);
        Route::delete('balance-increase/{balance}', [BalanceIncreaseController::class, 'delete']);

    //--------------------------- Factors ---------------------------
        Route::get('user/{user}/factors', [FactorController::class, 'getUserFactors']);
        Route::get('user/{user}/factors/delayed', [FactorController::class, 'getUserDelayedFactors']);
        Route::get('factor/{factor}', [FactorController::class, 'getFactor']);
        Route::post('user/{user}/factor', [FactorController::class, 'create']);
        Route::post('factor/{factor}', [FactorController::class, 'update']);
        Route::delete('factor/{factor}', [FactorController::class, 'delete']);
        Route::post('factor/{factor}/add-product', [FactorItemController::class, 'addProduct']);
        Route::delete('factor-product/{factorItem}', [FactorItemController::class, 'deleteProduct']);

    //------------------------- Installments ------------------------
        Route::get('factor/{factor}/installments', [InstallmentController::class, 'getFactorInstallments']);
        Route::post('factor/{factor}/installments', [InstallmentController::class, 'createInstallments']);
        Route::delete('factor/{factor}/installments', [InstallmentController::class, 'deleteInstallments']);
        Route::post('installment/{installment}/set-status', [InstallmentController::class, 'changeStatus']);

    //----------------------------- Organs -------------------------
        Route::get('organs', [OrganController::class, 'getAll']);
        Route::get('organ/{organ}', [OrganController::class, 'read']);

    //----------------------------- User Categories -------------------------
        Route::get('user-categories', [UserCategoryController::class, 'getAll']);
        Route::get('user-category/{category}', [UserCategoryController::class, 'read']);

    //----------------------------- Product Categories -------------------------
        Route::get('categories/all', [CatfirstController::class, 'getAllCategories']);
        //--- Cat First :
        Route::get('categories/first', [CatFirstController::class, 'getAll']);
        Route::get('categories/first/{category}', [CatFirstController::class, 'read']);
        Route::post('categories/first', [CatFirstController::class, 'create']);
        Route::post('categories/first/{category}', [CatFirstController::class, 'update']);
        Route::delete('categories/first/{category}', [CatFirstController::class, 'delete']);
        //--- Cat Second :
        Route::get('categories/first/{catFirst}/seconds', [CatSecondController::class, 'getAll']);
        Route::get('categories/second/{category}', [CatSecondController::class, 'read']);
        Route::post('categories/second', [CatSecondController::class, 'create']);
        Route::post('categories/second/{category}', [CatSecondController::class, 'update']);
        Route::delete('categories/second/{category}', [CatSecondController::class, 'delete']);
        //--- Cat Second :
        Route::get('categories/second/{catSecond}/thirds', [CatThirdController::class, 'getAll']);
        Route::get('categories/third/{category}', [CatThirdController::class, 'read']);
        Route::post('categories/third', [CatThirdController::class, 'create']);
        Route::post('categories/third/{category}', [CatThirdController::class, 'update']);
        Route::delete('categories/third/{category}', [CatThirdController::class, 'delete']);

    //----------------------------- Foreign Customer factor -------------------------
        Route::post('foreign-customer/factor', [FactorController::class, 'foreignFactor']);

    //----------------------------- Delay -----------------------------
        Route::get('delay/users/all', [InstallmentController::class, 'getAllDelayUsers']);
        Route::get('delay/users/current', [InstallmentController::class, 'getCurrentDelayUsers']);
        Route::get('delay/users/no-delay', [InstallmentController::class, 'getNoDelayUsers']);

});
