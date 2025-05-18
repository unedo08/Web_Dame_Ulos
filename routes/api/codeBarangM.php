<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CodeMController;

Route::get('/', [CodeMController::class, 'index']);
Route::get('/{id}', [CodeMController::class, 'show']);
Route::post('/', [CodeMController::class, 'store']);
Route::delete('/delete/{jenisbarang_id}', [CodeMController::class, 'destroy']);
