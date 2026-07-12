<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PewarnaMController;

Route::get('/all', [PewarnaMController::class, 'index']);
Route::get('/{id}', [PewarnaMController::class, 'show']);
Route::get('/', [PewarnaMController::class, 'getActive']);
Route::post('/', [PewarnaMController::class, 'store']);
Route::put('/{id}', [PewarnaMController::class, 'update']);
Route::delete('/{id}', [PewarnaMController::class, 'destroy']);
