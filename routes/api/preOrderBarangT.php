<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PreOrdeBarangTController;


Route::get('/', [PreOrdeBarangTController::class, 'index']);            
Route::post('/', [PreOrdeBarangTController::class, 'store']);          
Route::get('/{id}', [PreOrdeBarangTController::class, 'show']);         
Route::put('/{id}', [PreOrdeBarangTController::class, 'update']);       
Route::delete('/{id}', [PreOrdeBarangTController::class, 'destroy']);   
Route::patch('/{id}/status', [PreOrdeBarangTController::class, 'updateStatus']); 
