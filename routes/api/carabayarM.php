<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CaraBayarMController;

Route::get('/', [CaraBayarMController::class, 'index']);
Route::get('/{id}', [CaraBayarMController::class, 'show']);
Route::post('/', [CaraBayarMController::class, 'store']);
Route::delete('/{id}', [CaraBayarMController::class, 'destroy']);