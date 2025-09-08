<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function (){
    Route::prefix('/users')->group(function (){
        Route::get('/', [AuthController::class, 'index']);
    });
});

Route::get('/', function(){
    return response()->json(['message' => 'API is working']); 
});
