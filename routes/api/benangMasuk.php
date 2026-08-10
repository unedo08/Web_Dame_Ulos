<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BenangMasukController;

// Specific routes before /{id}
Route::get('/stok', [BenangMasukController::class, 'stok']);
Route::get('/sumber-warna', [BenangMasukController::class, 'sumberWarna']);
Route::get('/warna-pewarna-alam', [BenangMasukController::class, 'warnaPewarnaAlam']);

Route::get('/', [BenangMasukController::class, 'index']);
Route::post('/pewarna-alam', [BenangMasukController::class, 'storePewarnaAlam']);
Route::post('/textile', [BenangMasukController::class, 'storeTextile']);
Route::get('/{id}', [BenangMasukController::class, 'show']);
Route::put('/{id}', [BenangMasukController::class, 'update']);
Route::delete('/{id}', [BenangMasukController::class, 'destroy']);
