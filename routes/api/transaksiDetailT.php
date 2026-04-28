<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransaksiDetailTController;

Route::get('/', [TransaksiDetailTController::class, 'index']);
Route::post('/', [TransaksiDetailTController::class, 'store']);
Route::post('/updateStatusPenjualan/{id}', [TransaksiDetailTController::class, 'updateStatusPenjualan']);
Route::get('/{id}', [TransaksiDetailTController::class, 'show']);
Route::put('/{id}', [TransaksiDetailTController::class, 'update']);
Route::delete('/{id}', [TransaksiDetailTController::class, 'destroy']);
