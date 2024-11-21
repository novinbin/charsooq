<?php


use App\Http\Controllers\BalanceIncreaseController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DashtController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\FactorController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\OrganController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserCategoryController;
use App\Http\Controllers\FactorItemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'isAdmin'])->prefix('manager/')->group(function () {

    //---------------------------- Dasht ----------------------------
        Route::post('dasht/login', [DashtController::class, 'login']);
        Route::get('dasht/products', [DashtController::class, 'getProducts']);
        Route::get('dasht/invoices', [DashtController::class, 'getInvoices']);

    //-------------------- Foreign Customer factor -------------------
        Route::post('foreign-customer/factor', [FactorController::class, 'foreignFactor']);

    //------------------------- User Control ------------------------
        Route::get('users', [UserController::class, 'getAll']);
        Route::post('user', [UserController::class, 'managerUserCreate']);
        Route::get('user/{user}', [UserController::class, 'read']);
        Route::put('user/{user}', [UserController::class, 'update']);
        Route::delete('user/{user}', [UserController::class, 'delete']);
        Route::put('user/{user}/change-access', [UserController::class, 'changeAccess']);
        Route::get('category/{category}/users', [UserController::class, 'getUsersByCategory']);
        Route::get('organ/{organ}/users', [UserController::class, 'getUsersByCategory']);

    //----------------------- Balance Increase ----------------------
        Route::get('user/{user}/balance-increase', [BalanceIncreaseController::class, 'getUserRequestList']);
        Route::get('balance-increase/{balance}', [BalanceIncreaseController::class, 'getBalance']);
        Route::post('user/{user}/balance-increase', [BalanceIncreaseController::class, 'create']);
        Route::post('balance-increase/{balance}', [BalanceIncreaseController::class, 'update']);
        Route::delete('balance-increase/{balance}', [BalanceIncreaseController::class, 'delete']);
        Route::get('balance-increase-pending', [BalanceIncreaseController::class, 'getPending']);
        Route::post('balance-increase/{balance}/approve', [BalanceIncreaseController::class, 'approve']);
        Route::post('balance-increase/{balance}/reject', [BalanceIncreaseController::class, 'reject']);

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

    //------------------------ Discount Codes -----------------------
        Route::get('discount-codes', [DiscountController::class, 'getAllCodes']);
        Route::get('discount-codes/customers', [DiscountController::class, 'getCustomerDiscounts']);
        Route::get('discount-codes/products', [DiscountController::class, 'getProductDiscounts']);
        Route::get('discount-code/{discount}', [DiscountController::class, 'read']);
        Route::post('discount-code', [DiscountController::class, 'create']);
        Route::delete('discount-code/{discount}', [DiscountController::class, 'delete']);
        Route::post('discount-code/{discount}/sms-notify', [DiscountController::class, 'sendSms']);

    //---------------------------- Banners -------------------------
        Route::get('banners/{keys}', [BannerController::class, 'getBanners']);
        Route::post('banners', [BannerController::class, 'updateOrCreate']);
        Route::delete('banners/{keys}', [BannerController::class, 'delete']);

    //----------------------------- Organs -------------------------
        Route::get('organs', [OrganController::class, 'getAll']);
        Route::get('organ/{organ}', [OrganController::class, 'read']);
        Route::post('organ', [OrganController::class, 'create']);
        Route::post('organ/{organ}', [OrganController::class, 'update']);
        Route::delete('organ/{organ}', [OrganController::class, 'delete']);

    //----------------------------- User Categories -------------------------
        Route::get('user-categories', [UserCategoryController::class, 'getAll']);
        Route::get('user-category/{category}', [UserCategoryController::class, 'read']);
        Route::post('user-category', [UserCategoryController::class, 'create']);
        Route::post('user-category/{category}', [UserCategoryController::class, 'update']);
        Route::delete('user-category/{category}', [UserCategoryController::class, 'delete']);

    //----------------------------- Delay -----------------------------
        Route::get('delay/users/all', [InstallmentController::class, 'getAllDelayUsers']);
        Route::get('delay/users/current', [InstallmentController::class, 'getCurrentDelayUsers']);
        Route::get('delay/users/no-delay', [InstallmentController::class, 'getNoDelayUsers']);

    //-------------------------- Transactions --------------------------
        Route::get('transactions/today', [TransactionController::class, 'getTodayTransactions']);
        Route::get('transactions/week', [TransactionController::class, 'getWeekTransactions']);
        Route::get('transactions/month', [TransactionController::class, 'getMonthTransactions']);
        Route::get('transactions/all', [TransactionController::class, 'getAllTransactions']);
        Route::get('transactions/users', [TransactionController::class, 'transactionsOfUsers']);
        Route::get('transactions/users/{user}', [TransactionController::class, 'getUserTransactions']);


});
