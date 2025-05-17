<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarangEntryMController;

Route::get('/', [BarangEntryMController::class, 'index']);
Route::get('/{id}', [BarangEntryMController::class, 'show']);

// Route::delete('/{id}', [CodeMController::class, 'destroy']);
