<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LiveOrderTController;

// ---------- CRUD Routes for LiveOrderT ----------
Route::get('/', [LiveOrderTController::class, 'getDataLiveOrderWithBarangId']);    // Get all
Route::get('/show-live/{id}', [LiveOrderTController::class, 'show']);              // Get by ID
Route::post('/store-live', [LiveOrderTController::class, 'store']);                // Create
Route::put('/update-live/{id}', [LiveOrderTController::class, 'update']);          // Update
Route::delete('/delete-live/{id}', [LiveOrderTController::class, 'destroy']);      // Delete

Route::get('/data-live/akun/{namaAkun}', [LiveOrderTController::class, 'getLiveOrderByNamaAkun'])
    ->where('namaAkun', '.*');

// ---------- Custom Actions & Queries ----------
Route::patch('/{id}/check', [LiveOrderTController::class, 'updateStatusCheck']);

// Get Data & Datatables
Route::get('/data-table', [LiveOrderTController::class, 'getDataTabelLiveOrder']);
Route::get('/getAmountLive', [LiveOrderTController::class, 'countByNamaAkun']);

// Spesific Data Live Orders
Route::get('/data-live-grouped', [LiveOrderTController::class, 'getGroupedByNamaAkun']);

Route::get('/data-live/{id}', [LiveOrderTController::class, 'getLiveOrderByLiveId'])
    ->whereNumber('id');


