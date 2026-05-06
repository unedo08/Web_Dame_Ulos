<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlatformMController;

Route::get('/all', [PlatformMController::class, 'index']);
Route::get('/{id}', [PlatformMController::class, 'show']);
Route::get('/', [PlatformMController::class, 'getActive']);
Route::post('/', [PlatformMController::class, 'store']);
Route::put('/{id}', [PlatformMController::class, 'update']);
Route::delete('/{id}', [PlatformMController::class, 'destroy']);
