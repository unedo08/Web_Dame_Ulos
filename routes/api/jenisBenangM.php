<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JenisBenangMController;

Route::get('/all', [JenisBenangMController::class, 'index']);
Route::get('/{id}', [JenisBenangMController::class, 'show']);
Route::get('/', [JenisBenangMController::class, 'getActive']);
Route::post('/', [JenisBenangMController::class, 'store']);
Route::put('/{id}', [JenisBenangMController::class, 'update']);
Route::delete('/{id}', [JenisBenangMController::class, 'destroy']);
