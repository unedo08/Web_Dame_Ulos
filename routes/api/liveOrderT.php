<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LiveOrderTController;

// CRUD routes for LiveOrderT
Route::get('/', [LiveOrderTController::class, 'index']);        // Get all
Route::get('/show-live/{id}', [LiveOrderTController::class, 'show']);    // Get by ID
Route::post('/store-live', [LiveOrderTController::class, 'store']);       // Create
Route::put('/update-live/{id}', [LiveOrderTController::class, 'update']);  // Update
Route::delete('/delete-live/{id}', [LiveOrderTController::class, 'destroy']); // Delete
Route::get('/data-live/{namaAkun}', [LiveOrderTController::class, 'getLiveOrderByNamaAkun']);        // Get all
Route::get('/data-live/{id}', [LiveOrderTController::class, 'getLiveOrderByLiveId']);  

// Custom route to get count by nama akun
Route::get('/getAmountLive', [LiveOrderTController::class, 'countByNamaAkun']);
Route::get('/data-table', [LiveOrderTController::class, 'getDataTabelLiveOrder']); 
