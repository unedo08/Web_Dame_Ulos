<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CaraBayarMController;

Route::get('/all', [CaraBayarMController::class, 'index']);
Route::get('/{id}', [CaraBayarMController::class, 'show']);
Route::get('/', [CaraBayarMController::class, 'getActiveCarabayar']);
Route::post('/', [CaraBayarMController::class, 'store']);
Route::put('/{id}', [CaraBayarMController::class, 'update']);
Route::delete('/{id}', [CaraBayarMController::class, 'destroy']);
