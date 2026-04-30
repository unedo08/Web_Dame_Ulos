<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::get('/transaksi', [DashboardController::class, 'transaksiTypeChart']);
Route::get('/transaksi-platform', [DashboardController::class, 'transaksiPlatformChart']);
Route::get('/barang', [DashboardController::class, 'barangChart']);
Route::get('/jenis-barang-entry', [DashboardController::class, 'jenisBarangEntryChart']);
Route::get('/jenis-barang-jual', [DashboardController::class, 'jenisBarangJualChart']);
Route::get('/jumlah-customer', [DashboardController::class, 'jumlahCustomerChart']);
Route::get('/customer-chart', [DashboardController::class, 'customerChart']);