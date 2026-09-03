<?php

use App\Http\Controllers\Api\V1\SalePaymentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOptionController;
use App\Http\Controllers\ProductOptionValueController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /** Authentication Endpoints */
    Route::prefix('auth')->middleware('throttle:api')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    /** Shopfront Endpoints */
    Route::prefix('shopfront')->middleware('throttle:api')->group(function () {
        Route::get('products',[ProductController::class, 'index']);

        Route::get('products/{product}',[ProductController::class, 'show']);

        Route::get('categories',[CategoryController::class, 'index']);

        Route::get('brands',[BrandController::class, 'index']);
    });

    /** Protected Endpoints */
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        /** Users */
        Route::get('users', [UserController::class, 'index'])->middleware('permissionsblocker:users.view');
        Route::post('users', [UserController::class, 'store'])->middleware('permissionsblocker:users.create');
        Route::get('users/{user}',[UserController::class, 'show'])->middleware('permissionsblocker:users.view');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('permissionsblocker:users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permissionsblocker:users.delete');

        /** Branches */
        Route::get('branches', [BranchController::class, 'index'])->middleware('permissionsblocker:branches.view');
        Route::post('branches', [BranchController::class, 'store'])->middleware('permissionsblocker:branches.create');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->middleware('permissionsblocker:branches.view');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->middleware('permissionsblocker:branches.update');
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->middleware('permissionsblocker:branches.delete');

        /** Stores */
        Route::get('stores', [StoreController::class, 'index'])->middleware('permissionsblocker:stores.view');
        Route::post('stores', [StoreController::class, 'store'])->middleware('permissionsblocker:stores.create');
        Route::get('stores/{store}', [StoreController::class, 'show'])->middleware('permissionsblocker:stores.view');
        Route::put('stores/{store}', [StoreController::class, 'update'])->middleware('permissionsblocker:stores.update');
        Route::delete('stores/{store}', [StoreController::class, 'destroy'])->middleware('permissionsblocker:stores.delete');

        /** Product Categories */
        Route::get('categories', [CategoryController::class, 'index'])->middleware('permissionsblocker:categories.view');
        Route::post('categories', [CategoryController::class, 'store'])->middleware('permissionsblocker:categories.create');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('permissionsblocker:categories.view');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permissionsblocker:categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permissionsblocker:categories.delete');

        /** Brands */
        Route::get('brands', [BrandController::class, 'index'])->middleware('permissionsblocker:brands.view');
        Route::post('brands', [BrandController::class, 'store'])->middleware('permissionsblocker:brands.create');
        Route::get('brands/{brand}', [BrandController::class, 'show'])->middleware('permissionsblocker:brands.view');
        Route::put('brands/{brand}', [BrandController::class, 'update'])->middleware('permissionsblocker:brands.update');
        Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->middleware('permissionsblocker:brands.delete');

        /** Products */
        Route::get('products', [ProductController::class, 'index'])->middleware('permissionsblocker:products.view');
        Route::post('products',[ProductController::class, 'store'])->middleware('permissionsblocker:products.create');
        Route::get('products/{product}',[ProductController::class, 'show'])->middleware('permissionsblocker:products.view');
        Route::put('products/{product}',[ProductController::class, 'update'])->middleware('permissionsblocker:products.update');
        Route::delete('products/{product}',[ProductController::class, 'destroy'])->middleware('permissionsblocker:products.delete');

        /** Product Variants */
        Route::get('products/{product}/variants', [ProductVariantController::class, 'index'])->middleware('permissionsblocker:product_variants.view');
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->middleware('permissionsblocker:product_variants.create');
        Route::get('products/{product}/variants/{variant}', [ProductVariantController::class, 'show'])->middleware('permissionsblocker:product_variants.view');
        Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->middleware('permissionsblocker:product_variants.update');
        Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->middleware('permissionsblocker:product_variants.delete');

        /** Product Options */
        Route::get('products/{product}/options', [ProductOptionController::class, 'index'])->middleware('permissionsblocker:product_options.view');
        Route::post('products/{product}/options', [ProductOptionController::class, 'store'])->middleware('permissionsblocker:product_options.create');
        Route::get('products/{product}/options/{option}', [ProductOptionController::class, 'show'])->middleware('permissionsblocker:product_options.view');
        Route::put('products/{product}/options/{option}',[ProductOptionController::class, 'update'])->middleware('permissionsblocker:product_options.update');
        Route::delete('products/{product}/options/{option}', [ProductOptionController::class, 'destroy'])->middleware('permissionsblocker:product_options.delete');

        /** Product Options values */
        Route::get('products/{product}/options/{option}/values', [ProductOptionValueController::class, 'index'])->middleware('permissionsblocker:product_option_values.view');
        Route::post('products/{product}/options/{option}/values', [ProductOptionValueController::class, 'store'])->middleware('permissionsblocker:product_option_values.create');
        Route::get('products/{product}/options/{option}/values/{value}', [ProductOptionValueController::class, 'show'])->middleware('permissionsblocker:product_option_values.view');
        Route::put('products/{product}/options/{option}/values/{value}', [ProductOptionValueController::class, 'update'])->middleware('permissionsblocker:product_option_values.update');
        Route::delete('products/{product}/options/{option}/values/{value}', [ProductOptionValueController::class, 'destroy'])->middleware('permissionsblocker:product_option_values.delete');
        
        /** Inventory */
        Route::get('inventory', [StockController::class, 'index'])->middleware('permissionsblocker:inventory.view');
        Route::get('inventory/{stock}',[StockController::class, 'show'])->middleware('permissionsblocker:inventory.view');
        Route::post('inventory/opening',[StockController::class, 'opening'])->middleware('permissionsblocker:inventory.create');
        Route::post('inventory/adjust',[StockController::class, 'adjust'])->middleware('permissionsblocker:inventory.update');

        /** Stock Movements */
        Route::get('stock-movements',[StockMovementController::class, 'index'])->middleware('permissionsblocker:inventory.view');
        Route::get('stock-movements/{stockMovement}',[StockMovementController::class, 'show'])->middleware('permissionsblocker:inventory.view');

        /** Sales */
        Route::get('sales',[SaleController::class, 'index'])->middleware('permissionsblocker:sales.view');
        Route::post('sales',[SaleController::class, 'store'])->middleware('permissionsblocker:sales.create');
        Route::get('sales/{sale}',[SaleController::class, 'show'])->middleware('permissionsblocker:sales.view');

        /** Sale Payments */
        Route::get('sales/{sale}/payments',[SalePaymentController::class, 'index'])->middleware('permissionsblocker:sales.view');
        Route::post('sales/{sale}/payments',[SalePaymentController::class, 'store'])->middleware('permissionsblocker:sales.pay');

        /** Returns */
        Route::get('returns',[ReturnController::class, 'index'])->middleware('permissionsblocker:returns.view');
        Route::post('sales/{sale}/returns',[ReturnController::class, 'store'])->middleware('permissionsblocker:returns.create');
        Route::get('returns/{saleReturn}',[ReturnController::class, 'show'])->middleware('permissionsblocker:returns.view');

        /** Stock Transfers */
        Route::prefix('stock-transfers')->group(function () {
            Route::get('/', [StockTransferController::class, 'index'])->middleware('permissionsblocker:inventory.view');
            Route::post('/', [StockTransferController::class, 'store'])->middleware('permissionsblocker:inventory.transfer');
            Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->middleware('permissionsblocker:inventory.view');
        });

        Route::prefix('dashboard')->middleware('permissionsblocker:reports.view')->group(function () {
            Route::get('/', [DashboardController::class,'index']);
            Route::get('/sales-chart', [DashboardController::class,'salesChart']);
        });

        Route::prefix('reports')->middleware('permissionsblocker:reports.view')->group(function () {
            Route::get('/sales', [ReportController::class,'sales']);
            Route::get('/inventory', [ReportController::class,'inventory']);
            Route::get('/products', [ReportController::class,'products']);
            Route::get('/payments', [ReportController::class,'payments']);
            Route::get('/returns', [ReportController::class,'returns']);
        });
    });
});
