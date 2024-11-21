<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::get('/payment/installment/{installment}', [TransactionController::class, 'payInstallment']);
Route::get('/payment/factor/{factor}/installments', [TransactionController::class, 'payAllInstallments']);
Route::get('/payment/factor/{factor}', [TransactionController::class, 'payFactor']);

Route::get('/payment/verify', [TransactionController::class, 'verifyPayment']);

