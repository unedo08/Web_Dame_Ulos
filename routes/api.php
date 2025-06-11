<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
// Route::get('/getDataTable', [BarangEntryTempController::class, 'showDataTable']);
// Route::post('/jenisbarang', [JenisBarangMController::class, 'store']);
// Route::delete('/jenisbarang/{id}', [JenisBarangMController::class, 'destroy']);

Route::prefix('jenisbarang')->group(function () {
    require __DIR__.'/api/jenisBarangM.php';  // Relative path
});

Route::prefix('codebarang')->group(function () {
    require __DIR__.'/api/codeBarangM.php';  // Relative path
});

Route::prefix('entrybarangtemp')->group(function () {
    require __DIR__.'/api/entryBarangTemp.php';  // Relative path
});

Route::prefix('entrybarang')->group(function () {
    require __DIR__.'/api/entryBarangM.php';  // Relative path
});

Route::prefix('acara')->group(function () {
    require __DIR__.'/api/acaraM.php';  // Relative path
});

Route::prefix('acaradet')->group(function () {
    require __DIR__.'/api/acaradetM.php';  // Relative path
});

Route::prefix('transaksi')->group(function () {
    require __DIR__.'/api/transaksiT.php';  // Relative path
});

Route::prefix('transaksi-detail')->group(function () {
    require __DIR__.'/api/transaksiDetailT.php';  // Relative path
});

Route::prefix('pengiriman-barang')->group(function () {
    require __DIR__.'/api/pengirimanBarangT.php';  // Relative path
});

Route::prefix('/pre-order-barang')->group(function () {
    require __DIR__.'/api/preOrderBarangT.php';  // Relative path
});