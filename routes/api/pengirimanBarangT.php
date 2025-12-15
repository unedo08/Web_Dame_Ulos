<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PengirimanBarangTController;

Route::get('/export-data', [PengirimanBarangTController::class, 'exportPengirimanBarang']);
Route::get('/get-transaksi-detail/{id}', [PengirimanBarangTController::class, 'getPengirimanBarangByTransaksiId']);
Route::get('/get-pengiriman', [PengirimanBarangTController::class, 'getPengiriman']);
Route::get('/', [PengirimanBarangTController::class, 'index']); 
Route::post('/', [PengirimanBarangTController::class, 'store']); 
Route::get('/{id}', [PengirimanBarangTController::class, 'show']); 
Route::put('/{id}', [PengirimanBarangTController::class, 'update']); 
Route::delete('/{id}', [PengirimanBarangTController::class, 'destroy']); 
Route::patch('/{id}/status', [PengirimanBarangTController::class, 'updateStatus']); 
