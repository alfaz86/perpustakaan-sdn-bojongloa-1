<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/late-fee-receipt/{id}/download', [ReportController::class, 'lateFeeReceiptDownload'])
        ->name('late-fee-receipt.download');

    Route::get('/export/{export}/download', [ReportController::class, 'exportDownload'])
        ->name('export.download');
});
