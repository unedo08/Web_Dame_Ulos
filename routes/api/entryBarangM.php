<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangEntryMController;

Route::get('/getDataPO', [BarangEntryMController::class, 'getAllDataBarangPO']);
Route::post('/{id}/deleteBarangEntry', [BarangEntryMController::class, 'deleteBarangEntry']);
Route::post('/{id}/updateStok', [BarangEntryMController::class, 'updateStok']);
Route::get('/getDataReady', [BarangEntryMController::class, 'getAllDataBarangReady']);
Route::get('/getDataWaitForEntry', [BarangEntryMController::class, 'getAllDataBarangWaitToEntry']);
Route::get('/getDataPriceTag/{code_nama}', [BarangEntryMController::class, 'getDataPriceTag']);
Route::get('/getDataKasir/{code_nama}', [BarangEntryMController::class, 'getDataKasir']);
Route::post('/storeDescription', [BarangEntryMController::class, 'storeDescription']);
Route::post('/storeSize', [BarangEntryMController::class, 'storeSize']);
Route::get('/getJumlahBarang', [BarangEntryMController::class, 'getAllBarangEntryAmount']);
// Route::get('/checkBarangEntry/{code_nama}', [BarangEntryMController::class, 'checkBarangEntryByCodeNama']);
Route::patch('/{id}/updateStatus', [BarangEntryMController::class, 'updateStatusBarang']);
Route::get('/getDataByCode/{code_nama}', [BarangEntryMController::class, 'getDataByCode']);
Route::get('/', [BarangEntryMController::class, 'index']);
Route::get('/{id}', [BarangEntryMController::class, 'show']);



// Route::delete('/{id}', [CodeMController::class, 'destroy']);
