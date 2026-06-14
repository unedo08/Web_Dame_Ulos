<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangKeluarTController;

Route::get('/export',    [BarangKeluarTController::class, 'export']);
Route::get('/',          [BarangKeluarTController::class, 'index']);
Route::post('/',         [BarangKeluarTController::class, 'store']);
Route::get('/{id}',      [BarangKeluarTController::class, 'show']);
Route::post('/{id}/selesai', [BarangKeluarTController::class, 'selesai']);
Route::delete('/{id}',      [BarangKeluarTController::class, 'destroy']);
