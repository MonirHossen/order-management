<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\ProductController;
use Illuminate\Support\Facades\Route;

// API Version 1
Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Protected routes
    Route::middleware(['auth:api'])->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('profile', [AuthController::class, 'profile']);
        });

        // ============================================
        // PRODUCT ROUTES
        // ============================================
        
        // Public product viewing (all authenticated users)
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/search', [ProductController::class, 'search']);
            Route::get('/low-stock', [ProductController::class, 'lowStockProducts'])->middleware('role:admin,vendor');
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::get('/{id}/inventory-history', [ProductController::class, 'inventoryHistory'])->middleware('role:admin,vendor');
        });

        // Product management (admin & vendor only)
        Route::middleware(['role:admin,vendor'])->prefix('products')->group(function () {
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::post('/bulk-import', [ProductController::class, 'bulkImport']);
            Route::put('/{id}/inventory', [ProductController::class, 'updateInventory']);
        });

        // ============================================
        // ORDER ROUTES
        // ============================================
        Route::prefix('orders')->group(function () {
            // All authenticated users
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{id}', [OrderController::class, 'show']);
            Route::post('/', [OrderController::class, 'store']);
            Route::get('/{id}/status-history', [OrderController::class, 'statusHistory']);
            Route::get('/{id}/invoice', [OrderController::class, 'downloadInvoice']);
            
            // Cancel order (customer/admin)
            Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->middleware('role:customer,admin');
            
            // Update status (admin/vendor)
            Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('role:admin,vendor');
            
            // Statistics (admin only)
            Route::get('/statistics/overview', [OrderController::class, 'statistics'])->middleware('role:admin');
        });

        // Category routes
        // Route::prefix('categories')->group(function () {
        //     Route::get('/', [CategoryController::class, 'index']);
        //     Route::get('/tree', [CategoryController::class, 'tree']);
        //     Route::get('/stats', [CategoryController::class, 'stats'])->middleware('role:admin');
        //     Route::get('/{id}', [CategoryController::class, 'show']);
            
        //     Route::middleware(['role:admin'])->group(function () {
        //         Route::post('/', [CategoryController::class, 'store']);
        //         Route::put('/{id}', [CategoryController::class, 'update']);
        //         Route::put('/{id}/move', [CategoryController::class, 'move']);
        //         Route::delete('/{id}', [CategoryController::class, 'destroy']);
        //     });
        // });
    });
});