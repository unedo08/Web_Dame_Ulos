<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PackagingMController;

Route::get('/', [PackagingMController::class, 'index']); 
Route::post('/', [PackagingMController::class, 'store']); 
Route::get('/{id}', [PackagingMController::class, 'show']); 
Route::delete('/{id}', [PackagingMController::class, 'destroy']); 
Route::put('/{id}', [PackagingMController::class, 'update']); 
Route::post('/update-status/{id}', [PackagingMController::class, 'updateStatus']); 
