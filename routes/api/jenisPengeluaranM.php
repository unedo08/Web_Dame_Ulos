<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JenisPengeluaranMController;

Route::get('/all', [JenisPengeluaranMController::class, 'index']);
Route::get('/',    [JenisPengeluaranMController::class, 'getActive']);
Route::get('/{id}', [JenisPengeluaranMController::class, 'show']);
Route::post('/',   [JenisPengeluaranMController::class, 'store']);
Route::put('/{id}', [JenisPengeluaranMController::class, 'update']);
Route::delete('/{id}', [JenisPengeluaranMController::class, 'destroy']);
