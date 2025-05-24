<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangEntryMController;

Route::get('/getJumlahBarang', [BarangEntryMController::class, 'getAllBarangEntryAmount']);
Route::get('/checkBarangEntry/{code_nama}', [BarangEntryMController::class, 'checkBarangEntryByCodeNama']);
Route::patch('/{id}/updateStatus', [BarangEntryMController::class, 'updateStatusBarang']);
Route::get('/getDataByCode/{code_nama}', [BarangEntryMController::class, 'getDataByCode']);
Route::get('/', [BarangEntryMController::class, 'index']);
Route::get('/{id}', [BarangEntryMController::class, 'show']);



// Route::delete('/{id}', [CodeMController::class, 'destroy']);
