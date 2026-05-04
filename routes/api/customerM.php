<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerMController;

Route::get('/', [CustomerMController::class, 'getSummaryByCustomer']);
Route::get('/{id}/detail', [CustomerMController::class, 'getCustomerDetail']);
Route::get('/{id}', [CustomerMController::class, 'show']);
// Route::get('/getDataByAcara/{acara_id}', [CustomerMController::class, 'getByAcara']);
Route::post('/addCustomer', [CustomerMController::class, 'store']);
Route::delete('/deleteCustomer/{id}', [CustomerMController::class, 'destroy']);