<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\TransactionController;

Route::post('/login', [AuthController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    
    // User routes
    Route::prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:user.read');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create');
        Route::get('/{user}', [UserController::class, 'show'])->middleware('permission:user.read');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
        Route::post('/{user}/roles', [UserController::class, 'syncRoles'])->middleware('permission:user.set_role_permission');
    });
    
    // Sparepart routes - Admin access
    Route::prefix('/spareparts')->group(function () {
        // Admin routes
        Route::post('/', [SparepartController::class, 'store'])->middleware('permission:sparepart.create');
        Route::get('/{sparepart}', [SparepartController::class, 'show'])->middleware('permission:sparepart.read');
        Route::put('/{sparepart}', [SparepartController::class, 'update'])->middleware('permission:sparepart.update');
        Route::delete('/{sparepart}', [SparepartController::class, 'destroy'])->middleware('permission:sparepart.delete');
        
        // Both Admin and Staff can access these
        Route::get('/', [SparepartController::class, 'index'])->middleware('permission:sparepart.read');
        
        // Staff only route
        Route::get('/list/view', [SparepartController::class, 'list'])->middleware('permission:sparepart.read_list');
    });

    Route::prefix('/transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])
            ->middleware(['permission:transaction.read_all|transaction.read_own']);
        
        Route::post('/', [TransactionController::class, 'store'])
            ->middleware('permission:transaction.create');
        
        Route::get('/{transaction}', [TransactionController::class, 'show'])
            ->middleware(['permission:transaction.read_all|transaction.read_own']);
        
        Route::put('/{transaction}', [TransactionController::class, 'update'])
            ->middleware('permission:transaction.update_own');
        
        Route::post('/{transaction}/approve', [TransactionController::class, 'approve'])
            ->middleware('permission:transaction.approve');
        
        Route::post('/{transaction}/reject', [TransactionController::class, 'reject'])
            ->middleware('permission:transaction.reject');
    });
});

Route::get('/login', function() {
    return response()->json(['message' => 'API is working']); 
});