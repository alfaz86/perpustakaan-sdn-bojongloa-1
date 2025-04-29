<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));

Route::prefix('report')->name('report.')->group(function () {
    Route::get('/export/{export}/download', [ReportController::class, 'exportDownload'])
        ->name('export.download');
    Route::get('/late-fee-receipt/{id}/download', [ReportController::class, 'lateFeeReceipt'])
        ->name('late-fee-receipt');
    Route::get('/upload/{id}/download', [ReportController::class, 'uploadReport'])
        ->name('upload');
});
