<?php

use App\Http\Controllers\Api\SalesInvoice\SalesInvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// sales invoice
Route::prefix('v1')->group(function () {
    Route::get('/get/sales-invoices', [SalesInvoiceController::class, 'index']);
    Route::get('/get/sales-invoice/{id}', [SalesInvoiceController::class, 'show']);
    Route::post('/create/sales-invoice', [SalesInvoiceController::class, 'store']);
    Route::post('/update/sales-invoice/{id}', [SalesInvoiceController::class, 'update']);
    Route::delete('/delete/sales-invoice/{id}', [SalesInvoiceController::class, 'destroy']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
