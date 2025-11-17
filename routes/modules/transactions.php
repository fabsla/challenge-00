<?php

use App\Http\Controllers\Transactions\TransactionsController;
use Illuminate\Support\Facades\Route;


Route::controller(TransactionsController::class)->group(function () {
    
    Route::post('/deposit',     'deposit');
    Route::post('/withdrawal',  'withdrawal');
    Route::post('/transfer',    'transfer');
});