<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransaksiTController;


Route::get('/transaksi-summary', [TransaksiTController::class, 'transaksiSummary']);
Route::get('/status/hold', [TransaksiTController::class, 'getHoldTransactions']);
Route::get('/report', [TransaksiTController::class, 'getexportDataTransaksi']);
Route::get('/grouped', [TransaksiTController::class, 'getTransaksiGrouped']);

Route::get('/', [TransaksiTController::class, 'index']);
Route::post('/', [TransaksiTController::class, 'store']);

Route::patch('/{id}/status', [TransaksiTController::class, 'updateStatus']);
Route::delete('/{id}', [TransaksiTController::class, 'destroy']);
Route::get('/{id}', [TransaksiTController::class, 'show']); // paling bawah
