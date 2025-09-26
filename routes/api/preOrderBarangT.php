<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PreOrdeBarangTController;


Route::get('/', [PreOrdeBarangTController::class, 'index']);            
Route::post('/', [PreOrdeBarangTController::class, 'store']);          
Route::get('/kode-generator', [PreOrdeBarangTController::class, 'kodePO']);
Route::get('/{id}', [PreOrdeBarangTController::class, 'show']);         
Route::get('/preOrderEntry/{id}', [PreOrdeBarangTController::class, 'getPreOrderbyBarangEntryID']);         
Route::put('/{id}', [PreOrdeBarangTController::class, 'update']);       
Route::delete('/{id}', [PreOrdeBarangTController::class, 'destroy']);   
Route::patch('/{id}/status', [PreOrdeBarangTController::class, 'updateStatus']); 