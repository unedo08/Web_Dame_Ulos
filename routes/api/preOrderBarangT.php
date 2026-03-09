<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PreOrdeBarangTController;


Route::get('/kode-generator', [PreOrdeBarangTController::class, 'kodePO']);
Route::post('/storeImage', [PreOrdeBarangTController::class, 'storeImage']);
Route::get('/preOrderEntry/{id}', [PreOrdeBarangTController::class, 'getPreOrderbyBarangEntryID']);  
Route::get('/', [PreOrdeBarangTController::class, 'index']);            
Route::post('/', [PreOrdeBarangTController::class, 'store']);          
Route::get('/{id}', [PreOrdeBarangTController::class, 'show']);       
Route::put('/{id}', [PreOrdeBarangTController::class, 'update']);       
Route::delete('/{id}', [PreOrdeBarangTController::class, 'destroy']);   
Route::patch('/{id}/status', [PreOrdeBarangTController::class, 'updateStatus']); 
Route::get('/{id}/image', [PreOrdeBarangTController::class, 'viewImage']);
Route::post('/{id}/image', [PreOrdeBarangTController::class, 'updateImage']);
Route::delete('/{id}/image', [PreOrdeBarangTController::class, 'deleteImage']);