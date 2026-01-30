<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LiveOrderTController;

// CRUD routes for LiveOrderT
Route::get('/', [LiveOrderTController::class, 'getDataLiveOrderWithBarangId']);        // Get all
Route::get('/show-live/{id}', [LiveOrderTController::class, 'show']);    // Get by ID
Route::post('/store-live', [LiveOrderTController::class, 'store']);       // Create
Route::put('/update-live/{id}', [LiveOrderTController::class, 'update']);  // Update
Route::delete('/delete-live/{id}', [LiveOrderTController::class, 'destroy']); // Delete
// Route::get('/data-live/{id}', [LiveOrderTController::class, 'getLiveOrderByLiveId'])
//     ->whereNumber('id');
// Route::get('/data-live/{namaAkun}', [LiveOrderTController::class, 'getLiveOrderByNamaAkun'])->where('namaAkun', '.*');


// Custom route to get count by nama akun
Route::get('/getAmountLive', [LiveOrderTController::class, 'countByNamaAkun']);
Route::get('/data-table', [LiveOrderTController::class, 'getDataTabelLiveOrder']);
Route::patch('/{id}/check', [LiveOrderTController::class, 'updateStatusCheck']);

Route::get('/data-live/{id}', [LiveOrderTController::class, 'getLiveOrderByLiveId'])
    ->whereNumber('id');

Route::get('/data-live/{namaAkun}', [LiveOrderTController::class, 'getLiveOrderByNamaAkun'])
    ->where('namaAkun', '.*');
