<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\JenisBarangMController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/', function () {
    return response()->json(['message' => 'Hello world!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/user', [AuthController::class, 'updateUser']);
});

// // jenis barang
// Route::get('/jenisbarang', [JenisBarangMController::class, 'index']);
// Route::post('/jenisbarang', [JenisBarangMController::class, 'store']);
// Route::delete('/jenisbarang/{id}', [JenisBarangMController::class, 'destroy']);

Route::prefix('jenisbarang')->group(function () {
    require __DIR__.'/api/jenisBarangM.php';  // Relative path
});

Route::prefix('codebarang')->group(function () {
    require __DIR__.'/api/codeBarangM.php';  // Relative path
});