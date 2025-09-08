<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'store']);
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function (){
    Route::prefix('/users')->group(function (){
    });
});

Route::get('/login', function(){
    return response()->json(['message' => 'API is working']); 
});
