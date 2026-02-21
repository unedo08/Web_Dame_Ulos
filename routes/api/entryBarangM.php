<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangEntryMController;

/*
|--------------------------------------------------------------------------
| Custom Routes (HARUS DI ATAS)
|--------------------------------------------------------------------------
*/

// GET DATA
Route::get('/getDataPO', [BarangEntryMController::class, 'getAllDataBarangPO']);
Route::get('/getDataReady', [BarangEntryMController::class, 'getAllDataBarangReady']);
Route::get('/getDataWaitForEntry', [BarangEntryMController::class, 'getAllDataBarangWaitToEntry']);
Route::get('/getJumlahBarang', [BarangEntryMController::class, 'getAllBarangEntryAmount']);
Route::get('/getDataPriceTag/{code_nama}', [BarangEntryMController::class, 'getDataPriceTag']);
Route::get('/getDataKasir/{code_nama}', [BarangEntryMController::class, 'getDataKasir']);
Route::get('/getDataByCode/{code_nama}', [BarangEntryMController::class, 'getDataByCode']);

// STORE
Route::post('/storeDescription', [BarangEntryMController::class, 'storeDescription']);
Route::post('/storeSize', [BarangEntryMController::class, 'storeSize']);

// UPDATE KHUSUS
Route::put('/ready-stock-desc/{id}', [BarangEntryMController::class, 'updateReadyStockDesc'])
    ->whereNumber('id');

Route::put('/ready-stock-size/{id}', [BarangEntryMController::class, 'updateReadyStockSize'])
    ->whereNumber('id');

Route::put('/ready-stock/{id}', [BarangEntryMController::class, 'updateReadyStock'])
    ->whereNumber('id');

Route::patch('/{id}/updateStatus', [BarangEntryMController::class, 'updateStatusBarang'])
    ->whereNumber('id');

Route::post('/{id}/updateStok', [BarangEntryMController::class, 'updateStok'])
    ->whereNumber('id');

Route::post('/{id}/deleteBarangEntry', [BarangEntryMController::class, 'deleteBarangEntry'])
    ->whereNumber('id');


/*
|--------------------------------------------------------------------------
| Resource Basic (PALING BAWAH BIAR TIDAK BENTROK)
|--------------------------------------------------------------------------
*/

Route::get('/', [BarangEntryMController::class, 'index']);

Route::get('/{id}', [BarangEntryMController::class, 'show'])
    ->whereNumber('id');
