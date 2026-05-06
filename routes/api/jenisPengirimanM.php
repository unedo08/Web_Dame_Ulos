<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JenisPengirimanMController;

Route::get('/all', [JenisPengirimanMController::class, 'index']);
Route::get('/{id}', [JenisPengirimanMController::class, 'show']);
Route::get('/', [JenisPengirimanMController::class, 'getActive']);
Route::post('/', [JenisPengirimanMController::class, 'store']);
Route::put('/{id}', [JenisPengirimanMController::class, 'update']);
Route::delete('/{id}', [JenisPengirimanMController::class, 'destroy']);
