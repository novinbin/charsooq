<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FactorController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('user/')->group(function () {

    Route::get('/', [RegisteredUserController::class, 'show']);
    Route::put('/', [RegisteredUserController::class, 'update']);
    Route::get('/dashboard', [UserController::class, 'getDashboard']);

    //------------------------- Factors -------------------------
    Route::get('/factors', [FactorController::class, 'getMyFactors']);
    Route::get('/factors/{factor}', [FactorController::class, 'getFactor']);

    //----------------------- Installments ----------------------
    Route::get('/factors/{factor}/installments', [InstallmentController::class, 'getFactorInstallments']);

    //---------------------- Pay Installment --------------------
    Route::get('/factors/{factor}/calculate-remaining', [InstallmentController::class, 'calculateRemaining']);
    Route::get('/installment/{installment}/calculate-price', [InstallmentController::class, 'calculateInstallment']);

    //---------------------- Balance Increase --------------------
    Route::get('my-balance-increases', [UserController::class, 'myBalanceIncreases']);

});
