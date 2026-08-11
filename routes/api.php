<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShelfController;
use App\Http\Controllers\Api\StockCountController;
use App\Http\Controllers\Api\StockCountReportController;
use App\Http\Controllers\Api\StockImportController;
use App\Http\Controllers\Api\StockCountItemController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Barkod sorgulama - hem admin hem sayım personeli kullanabilir
    Route::get('/products/barcode/{barcode}', [ProductController::class, 'findByBarcode']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Sayımlar - listeleme/detay/kayıt girme (rol bazlı erişim controller içinde kontrol ediliyor)
    Route::get('/stock-counts', [StockCountController::class, 'index']);
    Route::get('/stock-counts/{stockCount}', [StockCountController::class, 'show']);
    Route::get('/stock-counts/{stockCount}/items', [StockCountItemController::class, 'index']);
    Route::post('/stock-counts/{stockCount}/items/scan', [StockCountItemController::class, 'scan']);
    Route::patch('/stock-counts/{stockCount}/items/{item}', [StockCountItemController::class, 'update']);

    // Yalnızca şirket admini
    Route::middleware('role:company_admin')->group(function () {
        Route::get('/branches', [BranchController::class, 'index']);
        Route::post('/branches', [BranchController::class, 'store']);
        Route::get('/branches/{branch}', [BranchController::class, 'show']);
        Route::patch('/branches/{branch}', [BranchController::class, 'update']);
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy']);

        Route::get('/branches/{branch}/warehouses', [WarehouseController::class, 'index']);
        Route::post('/branches/{branch}/warehouses', [WarehouseController::class, 'store']);
        Route::get('/branches/{branch}/warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::patch('/branches/{branch}/warehouses/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('/branches/{branch}/warehouses/{warehouse}', [WarehouseController::class, 'destroy']);

        Route::get('/branches/{branch}/warehouses/{warehouse}/shelves', [ShelfController::class, 'index']);
        Route::post('/branches/{branch}/warehouses/{warehouse}/shelves', [ShelfController::class, 'store']);
        Route::patch('/branches/{branch}/warehouses/{warehouse}/shelves/{shelf}', [ShelfController::class, 'update']);
        Route::delete('/branches/{branch}/warehouses/{warehouse}/shelves/{shelf}', [ShelfController::class, 'destroy']);

        Route::get('/imports', [StockImportController::class, 'index']);
        Route::post('/imports', [StockImportController::class, 'store']);

        Route::post('/stock-counts', [StockCountController::class, 'store']);
        Route::patch('/stock-counts/{stockCount}', [StockCountController::class, 'update']);
        Route::post('/stock-counts/{stockCount}/complete', [StockCountController::class, 'complete']);

        Route::get('/stock-counts/{stockCount}/report', [StockCountReportController::class, 'preview']);
        Route::get('/stock-counts/{stockCount}/report/export', [StockCountReportController::class, 'export']);
    });
});