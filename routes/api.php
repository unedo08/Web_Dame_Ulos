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
Route::post('/refresh', [AuthController::class, 'refresh'])
    ->middleware('jwt.refresh');


Route::middleware('jwt')->group(function () {
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::put('/user', [AuthController::class, 'updateUser']);
    Route::get('/getUser', [AuthController::class, 'getActiveUsers']);
    Route::get('/user/{id}', [AuthController::class, 'getUserById']);
    Route::put('/user/{id}', [AuthController::class, 'update']);

    Route::post('/user/update-password', [AuthController::class, 'updatePassword']);

    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);
});

Route::middleware(['jwt'])->group(function () {
    Route::prefix('/carabayar')->group(function () {
        require __DIR__ . '/api/carabayarM.php';
    });

    Route::prefix('/jenispengiriman')->group(function () {
        require __DIR__ . '/api/jenisPengirimanM.php';
    });

    Route::prefix('/platform')->group(function () {
        require __DIR__ . '/api/platformM.php';
    });

    Route::prefix('/jenisbenang')->group(function () {
        require __DIR__ . '/api/jenisBenangM.php';
    });

    Route::prefix('jenisbarang')->group(function () {
        require __DIR__ . '/api/jenisBarangM.php';  // Relative path
    });

    Route::prefix('codebarang')->group(function () {
        require __DIR__ . '/api/codeBarangM.php';  // Relative path
    });

    Route::prefix('entrybarangtemp')->group(function () {
        require __DIR__ . '/api/entryBarangTemp.php';  // Relative path
    });

    Route::prefix('entrybarang')->group(function () {
        require __DIR__ . '/api/entryBarangM.php';  // Relative path
    });

    Route::prefix('acara')->group(function () {
        require __DIR__ . '/api/acaraM.php';  // Relative path
    });

    Route::prefix('acaradet')->group(function () {
        require __DIR__ . '/api/acaradetM.php';  // Relative path
    });

    Route::prefix('transaksi')->group(function () {
        require __DIR__ . '/api/transaksiT.php';  // Relative path
    });

    Route::prefix('transaksi-detail')->group(function () {
        require __DIR__ . '/api/transaksiDetailT.php';  // Relative path
    });

    Route::prefix('pengiriman-barang')->group(function () {
        require __DIR__ . '/api/pengirimanBarangT.php';  // Relative path
    });

    // Route::prefix('pengiriman-barang-detail')->group(function () {
    //     require __DIR__ . '/api/pengirimanBarangDetailT.php';  // Relative path
    // });

    Route::prefix('/pre-order-barang')->group(function () {
        require __DIR__ . '/api/preOrderBarangT.php';  // Relative path
    });

    Route::prefix('/live-barang')->group(function () {
        require __DIR__ . '/api/liveOrderT.php';  // Relative path
    });

    Route::prefix('/packaging')->group(function () {
        require __DIR__ . '/api/packagingM.php';  // Relative path
    });

    Route::prefix('/customer')->group(function () {
        require __DIR__ . '/api/customerM.php';  // Relative path
    });

    Route::prefix('dashboard')->group(function () {
        require __DIR__ . '/api/dashboard.php';  // Relative path
    });

    Route::prefix('/jenis-pengeluaran')->group(function () {
        require __DIR__ . '/api/jenisPengeluaranM.php';
    });

    Route::prefix('/divisi')->group(function () {
        require __DIR__ . '/api/divisiM.php';
    });

    Route::prefix('/sumber-dana')->group(function () {
        require __DIR__ . '/api/sumberDanaM.php';
    });

    Route::prefix('/pengeluaran')->group(function () {
        require __DIR__ . '/api/pengeluaranT.php';
    });
});
