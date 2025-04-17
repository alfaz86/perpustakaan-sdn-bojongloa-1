<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/report/late-fee-receipt/{id}/download', [ReportController::class, 'lateFeeReceiptDownload'])
    ->name('late-fee-receipt.download');
