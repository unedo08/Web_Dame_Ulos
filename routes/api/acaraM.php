<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcaraMController;

Route::get('/', [AcaraMController::class, 'index']);
Route::get('/{id}', [AcaraMController::class, 'show']);
Route::post('/addAcara', [AcaraMController::class, 'addAcara']);
Route::put('/updateAcara/{id}', [AcaraMController::class, 'updateAcara']);
Route::put('/updateStatusAcara/{id}', [AcaraMController::class, 'updateStatus']);
Route::delete('/deleteAcara/{id}', [AcaraMController::class, 'destroy']);
Route::get('/export/{id}', [AcaraMController::class, 'exportData']);

