<?php

use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1/user/wallet')->group(function () {
    Route::get('', [WalletController::class, 'index']);
    Route::post('/balance/charge', [WalletController::class, 'deposit'])->name('wallet.balance.charge');
    Route::get('/balance/deposit/{user_id}/{amount}', [WalletController::class, 'verifyDeposit'])->name('wallet.deposit');
    Route::get('/analyze', [WalletController::class, 'analyze'])->name('wallet.analyze');
});
