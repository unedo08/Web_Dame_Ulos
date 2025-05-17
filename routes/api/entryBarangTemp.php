<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangEntryTempController;

Route::get('/getDataTable', [BarangEntryTempController::class, 'showDataTable']);
Route::get('/', [BarangEntryTempController::class, 'index']);
Route::get('/{id}', [BarangEntryTempController::class, 'show']);
Route::post('/storeDescription', [BarangEntryTempController::class, 'storeDescription']);
Route::post('/storeSize', [BarangEntryTempController::class, 'storeSize']);
Route::get('/storeToMaster/{code_id}', [BarangEntryTempController::class, 'storeToMaster']);

// Route::delete('/{id}', [CodeMController::class, 'destroy']);
