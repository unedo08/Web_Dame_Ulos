<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GrafikBenangController;

Route::get('/pertahun', [GrafikBenangController::class, 'pertahun']);
Route::get('/perbulan', [GrafikBenangController::class, 'perbulan']);
