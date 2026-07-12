<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BenangKeluarController;

Route::get('/export', [BenangKeluarController::class, 'export']);
Route::get('/', [BenangKeluarController::class, 'index']);
Route::post('/', [BenangKeluarController::class, 'store']);
Route::get('/{id}', [BenangKeluarController::class, 'show']);
Route::post('/{id}/selesai', [BenangKeluarController::class, 'selesai']);
Route::delete('/{id}', [BenangKeluarController::class, 'destroy']);
