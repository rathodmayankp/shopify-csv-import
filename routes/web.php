<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UploadController::class, 'create'])->name('uploads.create');
Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard/{upload}', [DashboardController::class, 'show'])->name('dashboard.show');

Route::get('/download-sample-csv', function () {
    $file = public_path('shopify-products.csv');
    return response()->download($file, 'shopify-products.csv');
})->name('sample.csv.download');;
