<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AcaradetMController;

Route::get('/', [AcaradetMController::class, 'index']);
Route::get('/{id}', [AcaradetMController::class, 'show']);
Route::get('/getDataByAcara/{acara_id}', [AcaradetMController::class, 'getByAcara']);
Route::post('/addDetAcara', [AcaradetMController::class, 'store']);
Route::delete('/deleteDetAcara/{id}', [AcaradetMController::class, 'destroy']);
Route::post('/addListBarangAcara', [AcaradetMController::class, 'addListCodeAcara']);
Route::get('/getListBarangAcara/{acara_id}', [AcaradetMController::class, 'getBarangEntryByAcara']);
