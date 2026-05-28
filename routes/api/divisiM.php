<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DivisiMController;

Route::get('/all', [DivisiMController::class, 'index']);
Route::get('/',    [DivisiMController::class, 'getActive']);
Route::get('/{id}', [DivisiMController::class, 'show']);
Route::post('/',   [DivisiMController::class, 'store']);
Route::put('/{id}', [DivisiMController::class, 'update']);
Route::delete('/{id}', [DivisiMController::class, 'destroy']);
