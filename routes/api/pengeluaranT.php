<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PengeluaranTController;

Route::get('/summary', [PengeluaranTController::class, 'summary']);
Route::get('/',        [PengeluaranTController::class, 'index']);
Route::get('/{id}',    [PengeluaranTController::class, 'show']);
Route::post('/',       [PengeluaranTController::class, 'store']);
Route::put('/{id}',    [PengeluaranTController::class, 'update']);
Route::delete('/{id}', [PengeluaranTController::class, 'destroy']);
