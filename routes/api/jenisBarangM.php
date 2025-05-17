<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JenisBarangMController;

Route::get('/', [JenisBarangMController::class, 'index']);
Route::post('/', [JenisBarangMController::class, 'store']);
Route::delete('/{id}', [JenisBarangMController::class, 'destroy']);
