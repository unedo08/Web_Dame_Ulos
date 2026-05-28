<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SumberDanaMController;

Route::get('/all', [SumberDanaMController::class, 'index']);
Route::get('/',    [SumberDanaMController::class, 'getActive']);
Route::get('/{id}', [SumberDanaMController::class, 'show']);
Route::post('/',   [SumberDanaMController::class, 'store']);
Route::put('/{id}', [SumberDanaMController::class, 'update']);
Route::delete('/{id}', [SumberDanaMController::class, 'destroy']);
